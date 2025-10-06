<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\ReadingExercise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReadingExerciseController extends Controller
{
    // GET /lessons/{lesson}/exercises
    public function index(Request $request, $lessonId)
    {
        $lesson = Lesson::findOrFail($lessonId);

        // ensure authenticated user owns this lesson (teacher)
        if ($request->user()->id !== $lesson->teacher_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden'
            ], 403);
        }

        $exercises = $lesson->exercises()->orderBy('created_at','asc')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Exercises fetched successfully',
            'data' => $exercises,
        ], 200);
    }

    // POST /exercises
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lesson_id' => 'required|exists:lessons,id',
            'text' => 'required|string|max:1000',
            'difficulty' => 'required|in:easy,medium,hard',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $lesson = Lesson::findOrFail($request->lesson_id);

        // ownership check
        if ($request->user()->id !== $lesson->teacher_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden',
            ], 403);
        }

        $exercise = ReadingExercise::create([
            'lesson_id' => $request->lesson_id,
            'text' => $request->text,
            'difficulty' => $request->difficulty,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Exercise created successfully',
            'data' => $exercise,
        ], 201);
    }

    // DELETE /exercises/{id}
    public function destroy(Request $request, $id)
    {
        $exercise = ReadingExercise::findOrFail($id);
        $lesson = $exercise->lesson;

        // ownership check
        if ($request->user()->id !== $lesson->teacher_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden',
            ], 403);
        }

        $exercise->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Exercise deleted successfully',
        ], 200);
    }

    public function update(Request $request, ReadingExercise $exercise)
    {
        // Optional: check if the user has permission to update
        // if ($request->user()->id !== $exercise->lesson->teacher_id) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        $data = $request->validate([
            'text' => 'required|string|max:1000',
        ]);

        $exercise->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Exercise updated successfully',
            'data' => $exercise,
        ]);
    }
}
