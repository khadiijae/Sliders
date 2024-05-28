<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Cloudinary\Cloudinary;
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
        $validatedData = $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories',
            'description' => 'required',

            'count' => 'nullable|integer',
            'image_cloudinary' => 'nullable|url',
        ]);
        if ($request->hasFile('image')) {
            $cloudinary = new Cloudinary();
            $uploadUrl = $cloudinary->uploadApi()->upload(
                $request->file('image')->getRealPath()
            );
            $validatedData['image_cloudinary'] = $uploadUrl['secure_url'];
        }
        Log::info('Validated data before image upload: ', $validatedData);

        $category = Category::create($validatedData);

        return response()->json(['message' => 'Category created successfully', 'category' => $category], 201);
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
        $category = Category::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,' . $category->id,
            'description' => 'required',
            'count' => 'nullable|integer',
            'image_cloudinary' => 'nullable|url',
        ]);

        if ($request->hasFile('image')) {
            $cloudinary = new Cloudinary();

            $uploadUrl = $cloudinary->uploadApi()->upload(
                $request->file('image')->getRealPath()
            );

            $validatedData['image_cloudinary'] = $uploadUrl['secure_url'];
        }

        $category->update($validatedData);
        Log::debug('Validated data before image upload: ', $validatedData);

        return response()->json(['message' => 'Category updated successfully', 'category' => $category], 200);
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
