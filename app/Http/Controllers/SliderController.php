<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Slider;
use App\Models\Sliderimages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class SliderController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'titre' => 'required|max:100|string',
                'description' => 'required|string',
                'url' => 'required|url',
                'images' => 'required',
                'images.*' => 'required|image|mimes:jpg,jpeg,png|max:50000'
            ]);
            $slider = Slider::create($validatedData);
            foreach ($request->file('images') as $image) {
                $cloudinaryImage = $image->storeOnCloudinary('SliderImages');
                $url = $cloudinaryImage->getSecurePath();
                $publicId = $cloudinaryImage->getPublicId();


                $sliderImage = Sliderimages::create([
                    'slider_id' => $slider->id,
                    'image_url' => $url,
                    'image_public_id' => $publicId
                ]);
            }

            return response()->json([
                'message' => 'Slider added successfully',
                'slider' => $slider
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed to create sliders', 'error' => $e->getMessage()], 500);
        }
    }
    public function show($id)
    {
        $slider = Slider::findOrFail($id);
        $images = Sliderimages::where('slider_id', $slider->id)->get();
        $slider->sliderImages = $images;
        return response()->json($slider);
    }
    public function index()
    {
        $sliders = Slider::all();
        $sliders->each(function ($slider) {

            $images = Sliderimages::where('slider_id', $slider->id)->get();
            $slider->sliderImages = $images;
        });
        return response()->json([
            'message' => 'All Sliders :',
            'slider' => $sliders
        ], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $slider = Slider::findOrFail($id);

            $validatedData = $request->validate([
                'titre' => 'required|string|max:100',
                'description' => 'required|string',
                'url' => 'required|url',
                'images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:50000'
            ]);



            // Delete oldimages from cldnry
            $slider->sliderImages()->get()->each(function ($image) {
                // Delete image from cldnry
                Cloudinary::destroy($image->image_public_id);
                $image->delete();
            });

            $slider->update($validatedData);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $cloudinaryImage = $image->storeOnCloudinary('sliderImages');
                    $url = $cloudinaryImage->getSecurePath();
                    $publicId = $cloudinaryImage->getPublicId();

                    $sliderImage = Sliderimages::create([
                        'slider_id' => $slider->id,
                        'image_url' => $url,
                        'image_public_id' => $publicId
                    ]);
                }
            }
            return response()->json([
                'message' => 'Slider updated successfully',
                'slider' => $slider
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function destroy(Request $request, $id)
    {
        try {
            // find imgaeid
            $slider = Slider::findOrFail($id);


            $slider->sliderImages()->get()->each(function ($image) {
                // Delete image from cldnry
                Cloudinary::destroy($image->image_public_id);
                $image->delete();
            });
            $slider->delete();
            return response()->json(['message' => 'Slider Deleted successfully'], 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Failed to delete Slider ', 'error' => $e->getMessage()], 500);
        }
    }
}
