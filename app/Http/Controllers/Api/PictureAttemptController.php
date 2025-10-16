<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PictureAttempt;
use Illuminate\Http\Request;

class PictureAttemptController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'picture_id' => 'required|exists:pictures,id',
            'attempts' => 'required|integer|min:1',
            'status' => 'required|in:success,failed',
            'type' => 'nullable|string'
        ]);

        $attempt = PictureAttempt::create($validated);

        return response()->json([
            'message' => 'Picture Guessing attempt recorded successfully',
            'data' => $attempt,
        ]);
    }
}
