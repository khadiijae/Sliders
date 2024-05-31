<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Str;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Models\OrderLineItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\ShippingAddressClient;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;


class CartController extends Controller
{


    public function createOrder(Request $request)
    {
        // Validate request data
        $validated = $request->validate([
            'fname' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',

            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'notes' => 'nullable|string',
            'paymentMethod' => 'required|string',
            'items' => 'required|array',
        ]);

        // Create shipping address
        $shippingAddress = ShippingAddressClient::create([
            'customer_id' => auth()->id(), // assuming the customer is authenticated
            'first_name' => $validated['fname'],
            'address_1' => $validated['address'],
            'address_2' => null, // assuming no address_2
            'city' => $validated['city'],
            'country' => 'MA',
            'phone' => $validated['phone'],
        ]);

        // Create order
        $order = Order::create([
            'status' => 'pending',
            'currency' => 'MAD',
            'version' => '1.0',
            'prices_include_tax' => false,
            'date_created' => Carbon::now(),
            'date_modified' => Carbon::now(),
            'discount_total' => 0, // assuming no discount
            'discount_tax' => 0, // assuming no discount tax
            'shipping_total' => 0, // assuming free shipping
            'shipping_tax' => 0, // assuming no shipping tax
            'cart_tax' => 0, // assuming no cart tax
            'total' => 0, // will be updated later
            'total_tax' => 0, // assuming no tax
            'customer_id' => auth()->id(),
            'order_key' => Str::random(12),
            'payment_method' => $validated['paymentMethod'],
            'payment_method_title' => ucfirst($validated['paymentMethod']),
            'transaction_id' => null, // will be updated later if needed
            'customer_ip_address' => $request->ip(),
            'customer_user_agent' => $request->header('User-Agent'),
            'created_via' => 'web',
            'customer_note' => $validated['notes'],
            'cart_hash' => null,
            'number' => 11111,
            'payment_url' => null,
            'is_editable' => true,
            'needs_payment' => true,
            'needs_processing' => true,
            'date_created_gmt' => Carbon::now(),
            'date_modified_gmt' => Carbon::now(),
        ]);

        // Initialize total amount
        $totalAmount = 0;

        // Create order line items
        foreach ($validated['items'] as $item) {
            $product = $item['product'][0]; // Assuming product is nested

            $lineItem = OrderLineItem::create([
                'order_id' => $order->id,
                'item_id' => $item['cart_item']['id'],
                'name' => $product['name'],
                'product_id' => $product['id'],
                'variation_id' => 0, // assuming no variation
                'quantity' => $item['cart_item']['quantity'],
                'subtotal' => $product['price'] * $item['cart_item']['quantity'],
                'subtotal_tax' => 0, // assuming no tax
                'total' => $product['price'] * $item['cart_item']['quantity'],
                'total_tax' => 0, // assuming no tax
                'sku' => $product['sku'] ?? null, // assuming sku is optional
                'price' => $product['price'],
                'image_url' => $product['image_url'] ?? null, // assuming image_url is optional
                'parent_name' => null, // assuming no parent
            ]);

            // Add to total amount
            $totalAmount += $lineItem->total;
        }

        // Update order total
        $order->total = $totalAmount;
        $order->save();

        return response()->json([
            'success' => true,
            'order' => $order,
        ], 201);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer',
        ]);

        $customerId = null;
        $userIp = null;

        try {
            //authenticate user with tokn
            $user = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            $user = null;
        }

        if ($user) {
            $customerId = $user->id;
            Log::info($customerId);
        } else {
            //   get the user's ipaddress
            $response = Http::get('https://api.ipify.org?format=json');
            $ipData = $response->json();
            $userIp = $ipData['ip'];
        }

        // Rechercher un élément existant dans le panier avec la même adresse IP et le même ID de produit
        $existingCart = Cart::where('product_id', $validated['product_id'])
            ->where(function ($query) use ($customerId, $userIp) {
                if ($customerId) {
                    $query->where('customer_id', $customerId);
                } else {
                    $query->where('user_ip', $userIp);
                }
            })
            ->first();

        if ($existingCart) {
            // Update  quantity of existing cartitem
            $existingCart->update([
                'quantity' => $existingCart->quantity + $validated['quantity'],
            ]);

            return response()->json([
                'message' => 'La quantité du produit a été mise à jour dans le panier avec succès',
                'cart' => $existingCart,
            ], 200);
        }

        // Créer une nouvelle entrée de panier si aucun élément avec la même adresse IP et le même ID de produit n'existe
        $cart = Cart::create([
            'product_id' => $validated['product_id'],
            'quantity' => $validated['quantity'],
            'customer_id' => $customerId,
            'user_ip' => $userIp,
        ]);

        Log::info('Cart details: ', $cart->toArray());

        return response()->json([
            'message' => 'Produit ajouté au panier avec succès',
            'cart' => $cart,
        ], 201);
    }




    public function getPanierData(Request $request)
    {
        $userIp = null;
        $customerId = null;
        $cartItems = collect();

        try {
            //authenticate user with tokn
            $user = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            $user = null;
        }

        if ($user) {
            // If the user is authenticated, get the customer ID
            $customerId = $user->id;

            //cartitems authenticated user
            $cartItems = Cart::where('customer_id', $customerId)->get();
        } else {
            $request->validate([
                'user_ip' => 'required|ip',
            ]);
            $userIp = $request->user_ip;

            //using  ipddress
            $cartItems = Cart::where('user_ip', $userIp)->get();
        }


        $cartData = [];


        foreach ($cartItems as $cartItem) {
            // product info
            $product = Product::find($cartItem->product_id);

            //product exists
            if ($product) {
                //  product images
                $images = ProductImage::where('product_id', $product->id)->get();
                $product->product_images = $images;

                // Add the cartitem and productdata to the array
                $cartData[] = [
                    'cart_item' => $cartItem,
                    'product' => $product,
                ];
            }
        }

        return response()->json([
            'message' => 'Données du panier récupérées avec succès',
            'cart_items' => $cartData,
        ], 200);
    }
}
