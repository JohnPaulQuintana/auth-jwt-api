<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SpellingActivity;
use Illuminate\Http\Request;

class SpellingActivityController extends Controller
{
    // List by lesson
    public function index(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|integer|exists:lessons,id'
        ]);

        $activities = SpellingActivity::where('lesson_id', $request->lesson_id)->get();

        return response()->json($activities);
    }

    // Create new
    public function store(Request $request)
    {
        $data = $request->validate([
            'lesson_id' => 'required|integer|exists:lessons,id',
            'word' => 'required|string|max:255',
            'image' => 'nullable|string',
            'missing_letter_indexes' => 'required|array|min:1',
            'missing_letter_indexes.*' => 'integer|min:0',
        ]);

        $activity = SpellingActivity::create($data);

        return response()->json([
            'message' => 'Spelling activity created successfully',
            'data' => $activity,
        ], 201);
    }


    // Update
    public function update(Request $request, $id)
    {
        $activity = SpellingActivity::findOrFail($id);

        $data = $request->validate([
            'word' => 'sometimes|string|max:255',
            'image' => 'nullable|string',
            'missing_letter_indexes' => 'sometimes|array|min:1',
            'missing_letter_indexes.*' => 'integer|min:0',
        ]);

        $activity->update($data);

        return response()->json([
            'message' => 'Spelling activity updated successfully',
            'data' => $activity
        ]);
    }


    // Delete
    public function destroy($id)
    {
        $activity = SpellingActivity::findOrFail($id);
        $activity->delete();

        return response()->json([
            'message' => 'Spelling activity deleted successfully'
        ]);
    }
}
