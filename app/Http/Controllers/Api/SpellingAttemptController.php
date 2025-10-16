<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PictureAttempt;
use App\Models\ReadingAttempt;
use App\Models\SpellingAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SpellingAttemptController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'spelling_activity_id' => 'required|exists:spelling_activities,id',
            'attempts' => 'required|integer|min:1',
            'status' => 'required|in:success,failed',
            'type' => 'nullable|string'
        ]);

        $attempt = SpellingAttempt::create($validated);

        return response()->json([
            'message' => 'Spelling attempt recorded successfully',
            'data' => $attempt,
        ]);
    }

    public function getByUser(Request $request, $user_id)
    {
        $spelling_attempts = SpellingAttempt::where('user_id', $user_id)
            ->with('spellingActivity.lesson')
            ->orderBy('created_at', 'desc')
            ->get();

        $picture_attempts = PictureAttempt::where('user_id', $user_id)
            ->with('pictureActivity.lesson')
            ->orderBy('created_at', 'desc')
            ->get();

        $reading_attempts = ReadingAttempt::where('user_id', $user_id)
            ->with('readingActivity.lesson')
            ->orderBy('created_at', 'desc')
            ->get();

        // Merge all attempts
        $all_attempts = $spelling_attempts->map(fn($a) => [
            'type' => 'spelling',
            'id' => $a->id,
            'attempts' => $a->attempts,
            'status' => $a->status,
            'created_at' => $a->created_at,
            'activity' => $a->spellingActivity,
        ])->merge(
            $picture_attempts->map(fn($a) => [
                'type' => 'picture',
                'id' => $a->id,
                'attempts' => $a->attempts,
                'status' => $a->status,
                'created_at' => $a->created_at,
                'activity' => $a->pictureActivity,
            ])
        )->merge(
            $reading_attempts->map(fn($a) => [
                'type' => 'reading',
                'id' => $a->id,
                'attempts' => $a->attempts,
                'status' => $a->status,
                'created_at' => $a->created_at,
                'activity' => $a->readingActivity,
            ])
        );

        // Sort by created_at descending
        $all_attempts = $all_attempts->sortByDesc('created_at')->values();

        // Log the merged collection for debugging
        Log::info('Merged user attempts:', $all_attempts->toArray());

        return response()->json([
            'message' => 'User attempts retrieved successfully',
            'data' => $all_attempts,
            'success' => true,
        ]);
    }
}
