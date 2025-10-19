<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found']);
        }

        $token = Password::broker()->createToken($user);

        $frontendUrl = config('app.frontend_url', config('app.url')); // e.g. https://myapp.com
        $webUrl = rtrim($frontendUrl, '/') . "/reset-password?token={$token}&email=" . urlencode($user->email);

        // Mail::to($user->email)->send(new ResetPasswordMail($webUrl));

        try {
            Mail::to($user->email)->send(new ResetPasswordMail($webUrl));
        } catch (\Exception $e) {
            \Log::error('Mail sending failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send email']);
        }

        return response()->json(['success' => true, 'message' => 'Reset link sent to email']);
    }

     /**
     * Handle password reset.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed', // ensures password_confirmation matches
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'Password has been reset successfully.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => __($status)
            ], 400);
        }
    }
}
