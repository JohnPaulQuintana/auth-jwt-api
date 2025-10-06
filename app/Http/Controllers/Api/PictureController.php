<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Picture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PictureController extends Controller
{
    // List all pictures for a lesson
    public function index(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
        ]);

        $pictures = Picture::where('lesson_id', $request->lesson_id)->get()->map(function ($pic) {
            $pic->image_url = url(Storage::url($pic->image_path));
            return $pic;
        });

        return response()->json(['data' => $pictures]);
    }

    // Create a picture
    public function store(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'title' => 'required|string|max:255',
            'image' => 'required|image|max:2048',
        ]);

        $path = $request->file('image')->store('pictures', 'public');

        $picture = Picture::create([
            'lesson_id' => $request->lesson_id,
            'title' => $request->title,
            'image_path' => $path,
        ]);

        $picture->image_url = url(Storage::url($picture->image_path));

        return response()->json(['data' => $picture], 201);
    }

    // Update a picture
    public function update(Request $request, Picture $picture)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'sometimes|image|max:2048',
        ]);

        $data = ['title' => $request->title];

        // If a new image is uploaded, delete old one and save new
        if ($request->hasFile('image')) {
            if ($picture->image_path && Storage::disk('public')->exists($picture->image_path)) {
                Storage::disk('public')->delete($picture->image_path);
            }
            $data['image_path'] = $request->file('image')->store('pictures', 'public');
        }

        $picture->update($data);
        $picture->image_url = url(Storage::url($picture->image_path));

        return response()->json(['data' => $picture]);
    }

    // Delete a picture
    public function destroy(Picture $picture)
    {
        if ($picture->image_path && Storage::disk('public')->exists($picture->image_path)) {
            Storage::disk('public')->delete($picture->image_path);
        }

        $picture->delete();

        return response()->json(['message' => 'Picture deleted']);
    }
}
