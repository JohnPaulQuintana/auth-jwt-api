<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class LessonController extends Controller
{

    public function index(Request $request)
    {
        $teacherId = $request->query('teacherId');

        if (!$teacherId) {
            return response()->json([
                'status' => 'error',
                'message' => 'teacherId parameter is required',
                'data' => null,
            ], 400);
        }

        $lessons = Lesson::where('teacher_id', $teacherId)->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Lessons fetched successfully',
            'data' => $lessons,
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'teacherId' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $lesson = Lesson::create([
            'teacher_id' => $request->teacherId,
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Lesson created successfully',
            'data' => $lesson,
        ], 201);
    }


    public function get_teacherId(Request $request, $id)
    {
        // Find the student
        $student = User::find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
                'data' => null,
                'errors' => ['student_id' => ['No student exists with the provided ID']]
            ], 404);
        }

        // Get teachers collection
        $teachers = $student->teachers;

        if ($teachers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No teachers assigned to this student',
                'data' => null,
                'errors' => ['teacher' => ['No teacher assigned to this student']]
            ]);
        }

        // Get first teacher's id
        $teacher = $teachers->first();
        $teacherId = $teacher->id;

        // Get lessons from that teacher
        $lessons = $teacher->lessons()->get(['id', 'title', 'description', 'teacher_id']);

        return response()->json([
            'success' => true,
            'message' => 'Teacher and lessons retrieved successfully',
            'data' => [
                'student_id' => $student->id,
                'teacher_id' => $teacherId,
                'lessons' => $lessons
            ],
            'errors' => null
        ]);
    }

    // Get all activities for a lesson
    public function getLessonActivities($lesson_id)
    {
        $lesson = Lesson::with(['spelling_activities', 'exercises', 'pictures'])->find($lesson_id);

        if (!$lesson) {
            return response()->json([
                'success' => false,
                'message' => 'Lesson not found',
                'errors' => ['lesson' => ['Lesson does not exist']],
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lesson activities retrieved successfully',
            'errors' => null,
            'data' => [
                'lesson_id' => $lesson->id,
                'title' => $lesson->title,
                'description' => $lesson->description,
                'spelling_activities' => $lesson->spelling_activities,
                'reading_exercises' => $lesson->exercises,
                'pictures' => $lesson->pictures,
            ],
        ]);
    }
}
