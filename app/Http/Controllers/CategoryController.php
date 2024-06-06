<?php

namespace App\Http\Controllers;

use App\Models\Category;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::all();
        return response()->json(['categories' => $categories], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validateRequest = $request->validate([
                'name' => ['required', 'max:255'],
                'slug' => ['required', 'max:255'],
                'description' => 'required',
                'count' => ['required', 'integer'],
                'image' => ['required', 'image', 'max:2048'],
            ]);

            $cloudinaryImage = $request->file('image')->storeOnCloudinary('categories');
            $url = $cloudinaryImage->getSecurePath();
            $public_id = $cloudinaryImage->getPublicId();

            $category = Category::create([
                'name' => $request->name,
                'slug' => $request->slug,
                'description' => $request->description,
                'count' => $request->count,
                'image_cloudinary' => $url,
                'image_public_id' => $public_id,
            ]);

            return response()->json([
                'message' => 'Category created successfully',
                'category' => $category
            ], 201);
        } catch (\Exception $e) {

            Log::error('Failed to create category: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create category', 'error' => $e->getMessage()], 500);
        }
    }





    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return response()->json(['category' => $category], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Find the category or fail if not found
        $category = Category::findOrFail($id);

        // Validate the incoming request data
        $validatedData = $request->validate([
            'name' => ['required', 'max:255'],
            'slug' => ['required', 'max:255'],
            'description' => 'required',
            'count' => ['required', 'integer'],
            'image' => ['image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            if ($category->image_public_id) {
                Cloudinary::destroy($category->image_public_id); //?delete old image
            }
            $cloudinaryImage = $request->file('image')->storeOnCloudinary('categories');
            $url = $cloudinaryImage->getSecurePath();
            $public_id = $cloudinaryImage->getPublicId();

            $category->image_cloudinary = $url;
            $category->image_public_id = $public_id;
        }

        $category->name = $validatedData['name'];
        $category->slug = $validatedData['slug'];
        $category->description = $validatedData['description'];
        $category->count = $validatedData['count'];

        $category->save();

        Log::debug($validatedData);

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category
        ], 200);
    }
    public function fetchRandomCategories()
    {
        $categories = Category::inRandomOrder()->limit(4)->get();
        return response()->json(['categories' => $categories], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(['message' => 'Category deleted successfully'], 200);
    }
}
