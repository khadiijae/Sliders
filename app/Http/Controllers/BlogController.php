<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Blogimages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::all();

        $blogs->each(function ($blog) {

            $images = Blogimages::where('blog_id', $blog->id)->get();
            $blog->Blogimages = $images;
        });
        return response()->json([
            'message' => 'All Blogs :',
            'Blogs ' => $blogs
        ], 200);
    }
    public function show($id)
    {
        $blog = Blog::findOrFail($id);
        $images = Blogimages::where('blog_id', $blog->id)->get();
        $blog->Blogimages = $images;
        return response()->json($blog);
    }
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'titre' => 'required|max:100|string',
                'description' => 'required|string',
                'contenu' => 'required|string',
                'images' => 'required',
                'images.*' => 'required|image|mimes:jpg,jpeg,png|max:50000'
            ]);
            $blog = Blog::create($validatedData);
            foreach ($request->file('images') as $image) {
                $cloudinaryImage = $image->storeOnCloudinary('BlogImages');
                $url = $cloudinaryImage->getSecurePath();
                $publicId = $cloudinaryImage->getPublicId();


                $blogImage = Blogimages::create([
                    'blog_id' => $blog->id,
                    'image_url' => $url,
                    'image_public_id' => $publicId
                ]);
            }

            return response()->json(['message' => 'Blog created successfully', 'blog' => $blog], 201);
        } catch (\Exception $e) {

            Log::error('Failed to create blog: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create blog', 'error' => $e->getMessage()], 500);
        }
    }



    public function update(Request $request, $id)
    {
        try {
            $blog = Blog::findOrFail($id);
            $validatedData = $request->validate([
                'titre' => 'required|max:100|string',
                'description' => 'required|string',
                'contenu' => 'required|string',
                'images' => 'required',
                'images.*' => 'required|image|mimes:jpg,jpeg,png|max:50000'
            ]);

            // Delete oldimages from cldnry
            $blog->blogImages()->get()->each(function ($image) {
                // Delete image from cldnry
                Cloudinary::destroy($image->image_public_id);
                $image->delete();
            });

            $blog->update($validatedData);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $cloudinaryImage = $image->storeOnCloudinary('BlogImages');
                    $url = $cloudinaryImage->getSecurePath();
                    $publicId = $cloudinaryImage->getPublicId();

                    $blogImage = Blogimages::create([
                        'blog_id' => $blog->id,
                        'image_url' => $url,
                        'image_public_id' => $publicId
                    ]);
                }
            }

            return response()->json(['message' => 'Blog updated successfully', 'blog' => $blog], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update blog', 'error' => $e->getMessage()], 500);
        }
    }
    public function destroy(Request $request, $id)
    {
        try {
            // find imgaeid
            $blog = Blog::findOrFail($id);


            $blog->blogImages()->get()->each(function ($image) {
                // Delete image from cldnry
                Cloudinary::destroy($image->image_public_id);
                $image->delete();
            });
            $blog->delete();
            return response()->json(['message' => 'Blog Deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Failed to delete blog ', 'error' => $e->getMessage()], 500);
        }
    }
}
