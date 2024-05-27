<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|max:100|string',
            'description' => 'required|string',
            'url' => 'required|url',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ]);
        $imagePath = $request->file('image')->store('sliders', 'public');

        $slider = Slider::create([
            'titre' => $request->titre,
            'description' => $request->description,
            'url' => $request->url,
            'image' => $imagePath
        ]);
        return response()->json([
            'message' => 'Slider added successfully',
            'slider' => $slider
        ], 200);
    }
    public function show($id)
    {
        $slider = Slider::findOrFail($id);
        return response()->json($slider);
    }
    public function index()
    {
        $sliders = Slider::all();
        return response()->json([
            'message' => 'All Sliders :',
            'slider' => $sliders
        ], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'titre' => 'required|string|max:100',
                'description' => 'required|string',
                'url' => 'required|url',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
            ]);

            $slider = Slider::findOrFail($id);

            if ($request->hasFile('image')) {
                Storage::disk('public')->delete($slider->image);
                $imagePath = $request->file('image')->store('sliders', 'public');
                $slider->image = $imagePath;
            }

            $slider->titre = $validatedData['titre'];
            $slider->description = $validatedData['description'];
            $slider->url = $validatedData['url'];
            $slider->save();

            return response()->json([
                'message' => 'Slider updated successfully',
                'slider' => $slider
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function destroy($id)
    {
        $slider = Slider::findOrFail($id);
        Storage::disk('public')->delete($slider->image);
        $slider->delete();
        return response()->json([
            'message' => 'Slider Deleted successfully',

        ], 200);
    }
}
