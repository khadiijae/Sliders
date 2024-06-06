<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = 26;

        $products = Product::with(['product_images', 'categorie'])->paginate($perPage);

        foreach ($products as $product) {
            $product->category_name = $product->categorie ? $product->categorie->name : null;
        }

        return response()->json($products);
    }


    public function store(Request $request)
    {

        try {
            // Validate the product data
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'store_id' => 'required|exists:vendors,id',
                'slug' => 'required|string|max:255|unique:products,slug',
                'status' => 'required|string|max:50',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'images.*' => 'nullable|image|mimes:jpeg,jpg,png|max:2048', // .* Validate multiple images
                'categorie_id' => 'required|exists:categories,id',
            ]);


            $product = Product::create($validatedData);


            Log::info('Product created: ' . json_encode($product));


            foreach ($request->file('images') as $image) {

                $cloudinaryImage = $image->storeOnCloudinary('productImage');
                $url = $cloudinaryImage->getSecurePath();
                $publicId = $cloudinaryImage->getPublicId();



                //  new ProductImage entry
                $productImage = ProductImage::create([
                    'product_id' => $product->id,
                    'image_url' => $url,
                    'image_public_id' => $publicId,
                ]);
            }

            return response()->json(['message' => 'Product created successfully', 'product' => $product], 201);
        } catch (\Exception $e) {

            Log::error('Failed to create product: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create product', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = Product::with(['product_images', 'categorie', 'store'])->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json([
            'product' => $product,
            'category_detail' => $product->categorie,
            'store_detail' => $product->store,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);


            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:products,slug,' . $product->id,
                'status' => 'required|string|max:50',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'images.*' => 'nullable|image|mimes:jpeg,jpg,png|max:2048', // .* Validate multiple images
                'categorie_id' => 'required|exists:categories,id',

            ]);

            $product->product_images()->get()->each(function ($image) {
                // Delete image from cldnry
                Cloudinary::destroy($image->image_public_id);
                $image->delete();
            });
            $product->update($validatedData);


            if ($request->hasFile('images')) {


                foreach ($request->file('images') as $image) {
                    $cloudinaryImage = $image->storeOnCloudinary('productImage');
                    $url = $cloudinaryImage->getSecurePath();
                    $publicId = $cloudinaryImage->getPublicId();


                    $productImage = ProductImage::create([
                        'product_id' => $product->id,
                        'image_url' => $url,
                        'image_public_id' => $publicId,
                    ]);
                }
            }

            return response()->json(['message' => 'Product updated successfully', 'product' => $product], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update product', 'error' => $e->getMessage()], 500);
        }
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
    public function destroy(Request $request, $id)
    {
        try {
            // find imgaeid
            $blog = Product::findOrFail($id);


            $blog->product_images()->get()->each(function ($image) {
                // Delete image from cldnry
                Cloudinary::destroy($image->image_public_id);
                $image->delete();
            });
            $blog->delete();
            return response()->json(['message' => 'Product Deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Failed to delete Product ', 'error' => $e->getMessage()], 500);
        }
    }

    public function searchQuery(Request $request)
    {
        $query =  Product::with('product_images');

        if ($request->filled('name')) {
            $query->where('name', 'LIKE', '%' . $request->name . '%');
        }

        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }

        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('average_rating')) {
            $query->where('average_rating', '=', $request->average_rating);
        }


        //?Tri par nom/prix/rating
        if ($request->filled('sort_by')) {
            $sortBy = $request->sort_by;
            $sortOrder = $request->sort_order ?? 'asc';

            if (in_array($sortBy, ['name', 'price', 'average_rating'])) {
                $query->orderBy($sortBy, $sortOrder);
            }
        }

        $perPage = $request->input('per_page', 10); // 10 items
        $products = $query->paginate($perPage);

        return response()->json($products);
    }



    public function getProductsByCategoryId($categoryId)
    {
        //check category exists
        $category = Category::find($categoryId);
        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        //  products with images
        $products = Product::where('categorie_id', $categoryId)
            ->with('product_images')
            ->paginate(10);

        return response()->json($products);
    }


    public function getProductsByStore($storeId)
    {
        $store = Vendor::find($storeId);
        if (!$store) {
            return response()->json(['message' => 'Store not found'], 404);
        }

        $products = Product::where('store_id', $storeId)
            ->with('product_images')
            ->paginate(5);

        return response()->json(['product' => $products]);
    }
}
