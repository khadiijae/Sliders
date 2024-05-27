<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    // Nombre de produits par page (vous pouvez ajuster cette valeur selon vos besoins)
    $perPage = 26;

    // Récupérer les produits paginés
    $products = Product::paginate($perPage);

    // Parcourir chaque produit et obtenir ses images
    $products->each(function ($product) {
        // Charger les images associées à ce produit
        $product->load('product_images');
    });

    return response()->json($products);
}
 
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            // Ajoutez ici les règles de validation pour chaque champ de votre formulaire
            // Par exemple:
            // 'name' => 'required',
            // 'price' => 'required|numeric',
        ]);

        $product = Product::create($validatedData);

        return response()->json(['message' => 'Product created successfully', 'product' => $product], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
{
    $product = Product::where('id', $id)->first();

    if (!$product) {
        return response()->json(['message' => 'Product not found'], 404);
    }

    // Charger les images associées à ce produit
    $product->load('product_images');

    return response()->json(['product' => $product]);
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validatedData = $request->validate([
            // Ajoutez ici les règles de validation pour chaque champ de votre formulaire
            // Par exemple:
            // 'name' => 'required',
            // 'price' => 'required|numeric',
        ]);

        $product->update($validatedData);

        return response()->json(['message' => 'Product updated successfully', 'product' => $product], 200);
    }

    public function randomProducts()
    {
        // Récupérer 15 produits aléatoires
        $randomProducts = Product::inRandomOrder()->limit(15)->get();
        
        return response()->json(['randomProducts' => $randomProducts], 200);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}
