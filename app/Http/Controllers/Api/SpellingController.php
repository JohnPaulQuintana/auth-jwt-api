<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Spelling;
use App\Http\Resources\SpellingResource;

class SpellingController extends Controller
{
    public function saveLevels(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'levels' => 'required|array',
        ]);

        $teacher_id = $request->teacher_id;
        $levels = $request->levels;

        foreach ($levels as $level) {
            // Use updateOrCreate with the database id if it exists
            Spelling::updateOrCreate(
                ['id' => $level['id'] ?? null], // null for new records
                [
                    'teacher_id' => $teacher_id,
                    'title' => $level['title'],
                    'description' => $level['description'] ?? '',
                    'icon' => $level['icon'] ?? 'book-outline',
                    'attempts' => $level['attempts'] ?? 3,
                    'letters_to_remove' => $level['lettersToRemove'] ?? [],
                    'score' => $level['score'] ?? 10,
                ]
            );
        }

        return response()->json(['message' => 'Levels saved successfully']);
    }



    public function getLevels(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
        ]);

        $teacherId = $request->teacher_id;

        // Fetch levels for the teacher ordered latest to oldest
        $levels = Spelling::where('teacher_id', $teacherId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Levels retrieved successfully',
            'data' => SpellingResource::collection($levels),
        ]);
    }
}
