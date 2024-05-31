<?php

namespace App\Http\Controllers;

use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProductimageController extends Controller
{

    public function index()
    {
        $prdImages = ProductImage::all();
        return response()->json(['All Products Images :', $prdImages]);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'product_id' => 'required|int',
                'images' => 'required',
                'images.*' => 'image|mimes:jpeg,jpg,png|max:2048',
            ]);

            $productImages = [];
            foreach ($request->file('images') as $image) {
                $cloudinaryImage = $image->storeOnCloudinary('productImage');
                $url = $cloudinaryImage->getSecurePath();
                $publicId = $cloudinaryImage->getPublicId();

                $productImage = ProductImage::create([
                    'product_id' => $request->product_id,
                    'image_url' => $url,
                    'image_public_id' => $publicId
                ]);

                $productImages[] = $productImage;
            }

            return response()->json(['message' => 'Product images created successfully', 'product_images' => $productImages], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create product images', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $imageId)
    {
        try {
            $validatedData = $request->validate([
                'product_id' => 'int',
                'image' => 'required|image|mimes:jpeg,jpg,png|max:2048',
            ]);

            $productImage = ProductImage::findOrFail($imageId);

            $result = Cloudinary::destroy($productImage->image_public_id);

            Log::info(json_encode($result));

            if (isset($result['error'])) {
                Log::error('Failed to delete old image  ' . $productImage->image_public_id);
                return response()->json(['message' => 'Failed to delete old image ', 'error' => $result['error']], 500);
            }

            // upload the new image
            $cloudinaryImage = $request->file('image')->storeOnCloudinary('productImage');
            $url = $cloudinaryImage->getSecurePath();
            $publicId = $cloudinaryImage->getPublicId();

            $productImage->image_url = $url;
            $productImage->image_public_id = $publicId;
            $productImage->save();


            return response()->json(['message' => 'Product image updated successfully', 'product_image' => $productImage], 200);
        } catch (\Exception $e) {
            Log::error('Exception while updating product image: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update product image', 'error' => $e->getMessage()], 500);
        }
    }



    public function show($id)
    {
        $productimg = ProductImage::FindOrFail($id);
        return response()->json(['Product Image ' => $productimg], 200);
    }

    public function destroy(Request $request, $imageId)
    {
        try {
            // find imgaeid
            $productImage = ProductImage::findOrFail($imageId);

            Log::info($imageId);

            $result = Cloudinary::destroy($productImage->image_public_id);

            Log::info('result' . json_encode($result));

            if ($result['result']) {
                $productImage->delete();

                return response()->json(['message' => 'Productimage deleted successfully'], 200);
            } else {
                Log::error('Failed to delete image  ' . $productImage->image_public_id);
                return response()->json(['message' => 'Failed to delete image from Cloudinary'], 500);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Failed to delete product image', 'error' => $e->getMessage()], 500);
        }
    }
}
