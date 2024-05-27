<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

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
            'parent_id' => 'nullable|integer',
            'count' => 'nullable|integer',
            'image_url' => 'nullable|url',
        ]);

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
    public function update(Request $request, Category $category)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,' . $category->id,
            'description' => 'required',
            'parent_id' => 'nullable|integer',
            'count' => 'nullable|integer',
            'image_url' => 'nullable|url',
        ]);

        $category->update($validatedData);

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
