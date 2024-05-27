<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;

use App\Models\Product;
class CartController extends Controller
{
    public function store(Request $request)
    {
        // Valider les données entrantes
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer',
            'user_ip' => 'required|ip',
        ]);

        // Rechercher un élément existant dans le panier avec la même adresse IP et le même ID de produit
        $existingCart = Cart::where('product_id', $request->product_id)
                            ->where('user_ip', $request->user_ip)
                            ->first();

        if ($existingCart) {
            // Si un élément existe déjà, mettez à jour la quantité
            $existingCart->update([
                'quantity' => $existingCart->quantity + $request->quantity,
            ]);

            // Retourner la réponse JSON
            return response()->json([
                'message' => 'La quantité du produit a été mise à jour dans le panier avec succès',
                'cart' => $existingCart,
            ], 200);
        }

        // Créer une nouvelle entrée de panier si aucun élément avec la même adresse IP et le même ID de produit n'existe
        $cart = Cart::create([
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'user_ip' => $request->user_ip,
        ]);

        // Retourner la réponse JSON
        return response()->json([
            'message' => 'Produit ajouté au panier avec succès',
            'cart' => $cart,
        ], 201);
    }
    
      public function getPanierData(Request $request)
    {
        // Valider l'adresse IP
        $request->validate([
            'user_ip' => 'required|ip',
        ]);

        // Récupérer les éléments du panier
        $cartItems = Cart::where('user_ip', $request->user_ip)->get();

        // Tableau pour stocker les données du panier
        $cartData = [];

        // Pour chaque élément du panier
        foreach ($cartItems as $cartItem) {
            // Récupérer les informations du produit
            $product = Product::where("id",intval($cartItem->product_id))->limit(1)->get();
           

            // Vérifier si le produit existe
            if ($product) {
                  $product->each(function ($p) {
        // Charger les images associées à ce produit
        $p->load('product_images');
    });

                // Ajouter les données du produit au tableau du panier
                $cartData[] = [
                    'cart_item' => $cartItem,
                    'product' => $product,
                ];
            }
        }

        // Retourner les données du panier sous forme de réponse JSON
        return response()->json([
            'message' => 'Données du panier récupérées avec succès',
            'cart_items' => $cartData,
        ], 200);
    }
}
