<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReadingAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReadingAttemptController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'reading_exercise_id' => 'required|exists:reading_exercises,id',
            'attempts' => 'required|integer|min:1',
            'status' => 'required|in:success,failed',
            'type' => 'nullable|string'
        ]);

        // Log the incoming payload
        Log::info('Reading attempt received', $request->all());

        $attempt = ReadingAttempt::create($validated);

        // Log after saving to DB
        Log::info('Reading attempt saved', ['id' => $attempt->id] + $validated);

        return response()->json([
            'message' => 'Reading attempt recorded successfully',
            'data' => $attempt,
        ]);
    }
}
