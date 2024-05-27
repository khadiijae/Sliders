<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Vendor;

class VendorController extends Controller
{
    public function index(Request $request)
{
    $perPage = $request->has('per_page') ? $request->per_page : 12; // Nombre d'éléments par page, par défaut 12
    $vendors = Vendor::paginate($perPage);
    
    // Récupérer le nombre de produits pour chaque vendeur
    $vendors->each(function ($vendor) {
        $vendor->num_products = Product::where('store_id', $vendor->id)->count();
    });

    return response()->json($vendors);
}


    public function store(Request $request)
    {
        return Vendor::create($request->all());
    }

    public function show($id)
{
    $vendor = Vendor::with('products.product_images')->find($id);
    if (!$vendor) {
        return response()->json(['message' => 'Vendor not found'], 404);
    }

    return response()->json($vendor);
}

    public function update(Request $request, $id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->update($request->all());
        return $vendor;
    }

    public function destroy($id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->delete();
        return 204;
    }
}
