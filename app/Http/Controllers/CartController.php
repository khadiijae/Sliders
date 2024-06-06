<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Mail\WelcomeEmail;
use Illuminate\Support\Str;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Models\OrderLineItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Models\ShippingAddressClient;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;


class CartController extends Controller
{

    public function createOrder(Request $request)
    {
        try {
            $customerId = null;
            $user = null;
            $connected = false;

            try {
                // authenticate   user with the token
                $user = JWTAuth::parseToken()->authenticate();
            } catch (JWTException $e) {
                // Authentication failed
                $user = null;
            }

            $validated = $request->validate([
                'address' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'notes' => 'nullable|string',
                'paymentMethod' => 'required|string',
                'items' => 'required|array',
                'items.*.product_id' => 'required|integer|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'register' => 'nullable|boolean',
                'firstname' => 'nullable|required_if:register,true|string|max:255',
                'lastname' => 'nullable|required_if:register,true|string|max:255',
                'password' => 'nullable|required_if:register,true|string|min:6|confirmed',
                'ville' => 'nullable|required_if:register,true|string|max:255',
            ]);

            if ($user) {
                $customerId = $user->id;
                $connected = true;
                Log::info($customerId);
            } elseif (isset($validated['register']) && $validated['register']) {
                // Register new user
                $response = Http::get('https://api.ipify.org?format=json');
                $ipData = $response->json();

                $newUser = User::create([
                    'firstname' => $validated['firstname'],
                    'lastname' => $validated['lastname'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'],
                    'ville' => $validated['ville'],
                    'ipadresse' => $ipData['ip'],
                    'password' => Hash::make($validated['password']),
                ]);

                // login the new user auto
                $customerId = $newUser->id;
                $connected = true;

                //   shipping address
                $shippingAddress = ShippingAddressClient::create([
                    'customer_id' => $customerId,
                    'first_name' => $newUser->firstname,
                    'address_1' => $validated['address'],
                    'address_2' => null,
                    'city' => $validated['city'],
                    'country' => 'MA',
                    'phone' => $validated['phone'],
                ]);
            }

            //   order
            $order = Order::create([
                'status' => 'pending',
                'currency' => 'MAD',
                'version' => '1.0',
                'prices_include_tax' => false,
                'date_created' => Carbon::now(),
                'date_modified' => Carbon::now(),
                'discount_total' => 0,
                'discount_tax' => 0,
                'shipping_total' => 0,
                'shipping_tax' => 0,
                'cart_tax' => 0,
                'total' => 0,
                'total_tax' => 0,
                'customer_id' => $customerId,
                'order_key' => Str::random(12),
                'payment_method' => $validated['paymentMethod'],
                'payment_method_title' => ucfirst($validated['paymentMethod']),
                'transaction_id' => null,
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
                'connected' => $connected,
            ]);

            // Initialize total amount
            $totalAmount = 0;

            //   orderlineitems
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);

                $lineItem = OrderLineItem::create([
                    'order_id' => $order->id,
                    'name' => $product->name,
                    'product_id' => $product->id,
                    'variation_id' => 0,
                    'quantity' => $item['quantity'],
                    'subtotal' => $product->price * $item['quantity'],
                    'subtotal_tax' => 0,
                    'total' => $product->price * $item['quantity'],
                    'total_tax' => 0,
                    'sku' => $product->sku ?? null,
                    'price' => $product->price,
                    'image_url' => $product->image_url ?? null,
                    'parent_name' => null,
                ]);

                $totalAmount += $lineItem->total;
            }

            // Update order total
            $order->total = $totalAmount;
            $order->save();

            // Send welcome email if user was registered
            if (isset($validated['register']) && $validated['register']) {
                Mail::to($newUser->email)->send(new WelcomeEmail($newUser));
                Log::info('Welcome email sent to ' . $newUser->email);
            }

            return response()->json([
                'success' => true,
                'order' => $order,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create order: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create order', 'error' => $e->getMessage()], 500);
        }
    }

    // public function createOrder(Request $request)
    // {
    //     try {
    //         $customerId = null;
    //         $user = null;
    //         $connected = false;

    //         try {
    //             // Attempt to authenticate the user with the token
    //             $user = JWTAuth::parseToken()->authenticate();
    //         } catch (JWTException $e) {
    //             // Authentication failed
    //             $user = null;
    //         }

    //         if ($user) {
    //             $customerId = $user->id;
    //             $connected = true;
    //             Log::info($customerId);
    //         }

    //         // Validate request data
    //         $validated = $request->validate([
    //             'address' => 'required|string|max:255',
    //             'city' => 'required|string|max:255',
    //             'email' => 'required|email|max:255',
    //             'phone' => 'required|string|max:20',
    //             'notes' => 'nullable|string',
    //             'paymentMethod' => 'required|string',
    //             'items' => 'required|array',
    //             'items.*.product_id' => 'required|integer|exists:products,id',
    //             'items.*.quantity' => 'required|integer|min:1'
    //         ]);

    //         // Create shipping address if user is authenticated
    //         if ($user) {
    //             $shippingAddress = ShippingAddressClient::create([
    //                 'customer_id' => $customerId,
    //                 'first_name' => $user->firstname,
    //                 'address_1' => $validated['address'],
    //                 'address_2' => null,
    //                 'city' => $validated['city'],
    //                 'country' => 'MA',
    //                 'phone' => $validated['phone'],
    //             ]);
    //         }

    //         // Create order
    //         $order = Order::create([
    //             'status' => 'pending',
    //             'currency' => 'MAD',
    //             'version' => '1.0',
    //             'prices_include_tax' => false,
    //             'date_created' => Carbon::now(),
    //             'date_modified' => Carbon::now(),
    //             'discount_total' => 0,
    //             'discount_tax' => 0,
    //             'shipping_total' => 0,
    //             'shipping_tax' => 0,
    //             'cart_tax' => 0,
    //             'total' => 0,
    //             'total_tax' => 0,
    //             'customer_id' => $customerId,
    //             'order_key' => Str::random(12),
    //             'payment_method' => $validated['paymentMethod'],
    //             'payment_method_title' => ucfirst($validated['paymentMethod']),
    //             'transaction_id' => null,
    //             'customer_ip_address' => $request->ip(),
    //             'customer_user_agent' => $request->header('User-Agent'),
    //             'created_via' => 'web',
    //             'customer_note' => $validated['notes'],
    //             'cart_hash' => null,
    //             'number' => 11111,
    //             'payment_url' => null,
    //             'is_editable' => true,
    //             'needs_payment' => true,
    //             'needs_processing' => true,
    //             'date_created_gmt' => Carbon::now(),
    //             'date_modified_gmt' => Carbon::now(),
    //             'connected' => $connected,
    //         ]);

    //         // Initialize total amount
    //         $totalAmount = 0;

    //         // Create order line items
    //         foreach ($validated['items'] as $item) {
    //             $product = Product::find($item['product_id']);

    //             $lineItem = OrderLineItem::create([
    //                 'order_id' => $order->id,
    //                 'name' => $product->name,
    //                 'product_id' => $product->id,
    //                 'variation_id' => 0,
    //                 'quantity' => $item['quantity'],
    //                 'subtotal' => $product->price * $item['quantity'],
    //                 'subtotal_tax' => 0,
    //                 'total' => $product->price * $item['quantity'],
    //                 'total_tax' => 0,
    //                 'sku' => $product->sku ?? null,
    //                 'price' => $product->price,
    //                 'image_url' => $product->image_url ?? null,
    //                 'parent_name' => null,
    //             ]);

    //             $totalAmount += $lineItem->total;
    //         }

    //         // Update order total
    //         $order->total = $totalAmount;
    //         $order->save();

    //         return response()->json([
    //             'success' => true,
    //             'order' => $order,
    //         ], 201);
    //     } catch (\Exception $e) {
    //         Log::error('Failed to create order: ' . $e->getMessage());
    //         return response()->json(['message' => 'Failed to create order', 'error' => $e->getMessage()], 500);
    //     }
    // }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer',
        ]);

        try {
            //authenticate user with tokn
            $user = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'User not authenticated'
            ], 401);
        }

        $customerId = $user->id;
        Log::info($customerId);

        // Rechercher un élément existant dans le panier avec la même adresse IP et le même ID de produit
        $existingCart = Cart::where('product_id', $validated['product_id'])
            ->where('customer_id', $customerId)
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
        ]);

        Log::info('Cart details: ', $cart->toArray());

        return response()->json([
            'message' => 'Produit ajouté au panier avec succès',
            'cart' => $cart,
        ], 201);
    }




    public function getPanierData(Request $request)
    {
        try {
            // Authenticate user with the token
            $token = $request->bearerToken();
            $user = JWTAuth::setToken($token)->authenticate();
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'User not authenticated'
            ], 401);
        }

        // If the user is authenticated, get the customer ID
        $customerId = $user->id;

        //cartitems authenticated user
        $cartItems = Cart::where('customer_id', $customerId)->get();

        $cartData = [];

        foreach ($cartItems as $cartItem) {
            // product info
            $product = Product::with('product_images')->find($cartItem->product_id);
            //product exists
            if ($product) {

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


    public function updateCartItem(Request $request, $cartItemId)
    {
        try {

            $validated = $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);

            $customerId = null;
            $userIp = null;

            try {

                $user = JWTAuth::parseToken()->authenticate();
            } catch (JWTException $e) {
                $user = null;
            }

            if ($user) {
                $customerId = $user->id;
            } else {

                $response = Http::get('https://api.ipify.org?format=json');
                $ipData = $response->json();
                $userIp = $ipData['ip'];
            }

            // Find the cartittem
            $cartItem = Cart::where('id', $cartItemId)
                ->where(function ($query) use ($customerId, $userIp) {
                    if ($customerId) {
                        $query->where('customer_id', $customerId);
                    } else {
                        $query->where('user_ip', $userIp);
                    }
                })
                ->first();

            if (!$cartItem) {
                return response()->json([
                    'message' => 'Cat Item Not Found',
                ], 404);
            }

            // Update quantity
            $cartItem->quantity = $validated['quantity'];
            $cartItem->save();

            return response()->json([
                'message' => 'Cart item quantity updated successfully',
                'cart' => $cartItem,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to update cart item: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update cart item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteCartItem($cartItemId)
    {
        try {

            $user = JWTAuth::parseToken()->authenticate();
            $customerId = $user->id;

            // Find the cartitem
            $cartItem = Cart::where('id', $cartItemId)
                ->where('customer_id', $customerId)
                ->first();

            if (!$cartItem) {
                return response()->json([
                    'message' => 'Cart item not found',
                ], 404);
            }


            $cartItem->delete();

            return response()->json([
                'message' => 'Cart item deleted successfully',
            ], 200);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Failed to authenticate user',
                'error' => $e->getMessage(),
            ], 401);
        } catch (\Exception $e) {
            Log::error('Failed to delete cart item: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to delete cart item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getOrderDetails($orderId)
    {
        $order = Order::with([
            'customer',
            'orderLineItems.product.product_images',
            'shippingAddress'
        ])->find($orderId);

        return $order ? response()->json([
            'order' => $order,
            'user' => $order->customer,
            'items' => $order->orderLineItems->map->only(['product', 'quantity', 'subtotal', 'total']),
            'shipping_address' => $order->shippingAddress,
        ]) : response()->json(['message' => 'Order not found'], 404);
    }
}
