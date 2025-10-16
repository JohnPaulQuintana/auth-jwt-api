<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lesson;
use App\Models\Picture;
use App\Models\ReadingExercise;
use App\Models\SpellingActivity;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
        $lesson = Lesson::with(['spelling_activities.attempts', 'exercises.attempts', 'pictures.attempts'])->find($lesson_id);

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

    public function lesson_and_student_records(Request $request, $id)
    {
        $authUser = $request->user();

        // Fetch lessons for this teacher
        $lessons = Lesson::where('teacher_id', $id)->get();

        // Fetch students if user can
        $students = [];
        if (in_array($authUser->role, ['teacher', 'developer'])) {
            $students = $authUser->students; // fetch collection
        }

        // Count lessons per type
        $lessonCounts = [
            'SpellingMain' => $lessons->count(),
            'reading'     => $lessons->count(),
            'picture'     => $lessons->count(),
        ];

        return response()->json([
            'success' => true,
            'teacher_id' => $id,
            'lessons' => $lessonCounts,
            'students' => ["students" => $students->count()],
        ]);
    }

    //home screen for teacher
    public function home_screen(Request $request, $id)
    {
        $authUser = $request->user();

        // Fetch all lessons for this teacher
        $lessons = Lesson::where('teacher_id', $id)->pluck('id'); // just the IDs

        // Count related activities by lesson_id
        $readingCount = ReadingExercise::whereIn('lesson_id', $lessons)->count();
        $spellingCount = SpellingActivity::whereIn('lesson_id', $lessons)->count();
        $pictureCount = Picture::whereIn('lesson_id', $lessons)->count();

        // Total activities
        $totalActivities = $readingCount + $spellingCount + $pictureCount;

        // Fetch students if user can
        $students = collect();
        if (in_array($authUser->role, ['teacher', 'developer'])) {
            $students = $authUser->students; // Eloquent relation
        }

        return response()->json([
            "success" => true,
            "data" => [
                "totalStudents" => $students->count(),
                "activeStudents" => $students->count(), // adjust if you track active status
                "totalLessons" => $lessons->count(),
                "readingActivities" => $readingCount,
                "spellingActivities" => $spellingCount,
                "pictureActivities" => $pictureCount,
                "totalActivities" => $totalActivities,
            ]
        ]);
    }

    public function report_screen(Request $request, $id)
    {
        $authUser = $request->user();
        $lessons = Lesson::where('teacher_id', $id)->get();
        $lessonIds = $lessons->pluck('id');

        // 🔹 Reading Activities
        $reading = ReadingExercise::whereIn('lesson_id', $lessonIds)
            ->select('id', 'lesson_id', 'text as activity_title')
            ->get()
            ->flatMap(function ($item) {
                return DB::table('reading_attempts')
                    ->where('reading_exercise_id', $item->id)
                    ->select('attempts', 'type', 'created_at', 'user_id')
                    ->get()
                    ->map(function ($attempt) use ($item) {
                        return [
                            'id' => $item->id,
                            'user_id' => $attempt->user_id,
                            'lesson_id' => $item->lesson_id,
                            'activity_type' => $attempt->type ?? 'reading',
                            'activity_title' => $item->activity_title,
                            'attempts' => $attempt->attempts ?? 0,
                            'created_at' => $attempt->created_at,
                            'completed' => true,
                        ];
                    });
            });

        // 🔹 Spelling Activities
        $spelling = SpellingActivity::whereIn('lesson_id', $lessonIds)
            ->select('id', 'lesson_id', 'word as activity_title')
            ->get()
            ->flatMap(function ($item) {
                return DB::table('spelling_attempts')
                    ->where('spelling_activity_id', $item->id)
                    ->select('attempts', 'type', 'created_at', 'user_id')
                    ->get()
                    ->map(function ($attempt) use ($item) {
                        return [
                            'id' => $item->id,
                            'user_id' => $attempt->user_id,
                            'lesson_id' => $item->lesson_id,
                            'activity_type' => $attempt->type ?? 'spelling',
                            'activity_title' => $item->activity_title,
                            'attempts' => $attempt->attempts ?? 0,
                            'created_at' => $attempt->created_at,
                            'completed' => true,
                        ];
                    });
            });

        // 🔹 Picture Guessing Activities
        $pictures = Picture::whereIn('lesson_id', $lessonIds)
            ->select('id', 'lesson_id', 'title as activity_title')
            ->get()
            ->flatMap(function ($item) {
                return DB::table('picture_attempts')
                    ->where('picture_id', $item->id)
                    ->select('attempts', 'type', 'created_at', 'user_id')
                    ->get()
                    ->map(function ($attempt) use ($item) {
                        return [
                            'id' => $item->id,
                            'user_id' => $attempt->user_id,
                            'lesson_id' => $item->lesson_id,
                            'activity_type' => $attempt->type ?? 'picture',
                            'activity_title' => $item->activity_title,
                            'attempts' => $attempt->attempts ?? 0,
                            'created_at' => $attempt->created_at,
                            'completed' => true,
                        ];
                    });
            });

        // 🔹 Merge all activities
        $allActivities = collect()
            ->concat($reading)
            ->concat($spelling)
            ->concat($pictures)
            ->sortByDesc('created_at')
            ->values();

        // 🔹 Students
        $students = $authUser->students ?? collect();
        // $activeStudents = $students->where('status', 'active')->count();

        return response()->json([
            "overview" => [
                "totalStudents" => $students->count(),
                "activeStudents" => $students->count(),
                "totalLessons" => $lessons->count(),
                "totalAttempts" => $allActivities->count(),
            ],
            "lessons" => [
                [
                    "id" => 1,
                    "title" => "Reading Activities",
                    "description" => "Foundation words and phrases",
                    "totalStudents" => $students->count(),
                    "completedStudents" => rand(10, $students->count()),
                    "averageScore" => 85,
                    "attempts" => $reading->count(),
                ],
                [
                    "id" => 2,
                    "title" => "Spelling Activities",
                    "description" => "Learning words through spelling",
                    "totalStudents" => $students->count(),
                    "completedStudents" => rand(10, $students->count()),
                    "averageScore" => 78,
                    "attempts" => $spelling->count(),
                ],
                [
                    "id" => 3,
                    "title" => "Picture Guessing Activities",
                    "description" => "Fun with pictures and words",
                    "totalStudents" => $students->count(),
                    "completedStudents" => rand(10, $students->count()),
                    "averageScore" => 72,
                    "attempts" => $pictures->count(),
                ],
            ],
            "students" => $students->map(function ($student) use ($allActivities) {
                $studentAttempts = $allActivities->where('user_id', $student->id)->values();

                return [
                    "id" => $student->id,
                    "name" => $student->name,
                    "completedLessons" => rand(1, 10),
                    "totalAttempts" => $studentAttempts->count(),
                    "totalLessons" => rand(20, 100),
                    "lastActivity" => optional($studentAttempts->first())['created_at'] ?? null,
                    "attemptRecords" => $studentAttempts,
                ];
            })->values(),
        ]);
    }
}
