<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Mail\UserCreatedMail;
use App\Traits\ApiResponse;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TeacherController extends Controller
{
    use ApiResponse;
    // get teachers
    public function getTeachers(Request $request)
    {
        $perPage = $request->query('per_page', 100);
        $authUser = auth()->user();

        // Only allow certain roles to view teachers
        if (in_array($authUser->role, ['developer', 'admin'])) {
            $teachers = \App\Models\User::where('role', 'teacher')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return $this->paginated($teachers, 'Teachers fetched successfully');
        }

        // Other roles → forbidden
        return response()->json([
            'success' => false,
            'message' => 'You do not have permission to view teachers.',
        ], 403);
    }

    public function storeTeacher(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'nullable|string|min:8', // optional
            'role' => 'required|in:user,administrator,dev,teacher',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'teacher_id' => 'required_if:role,user|exists:users,id', // link student to teacher
        ]);

        // Handle profile picture
        $profilePicturePath = null;
        if ($request->hasFile('profile_picture')) {
            $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        // Create the user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => isset($data['password'])
                ? Hash::make($data['password'])
                : Hash::make(bin2hex(random_bytes(8))),
            'role' => $data['role'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'profile_picture' => $profilePicturePath,
        ]);
        // Create password reset token so user can set their password
        $token = Password::broker()->createToken($user);
        $frontendUrl = config('app.frontend_url', config('app.url'));
        $resetUrl = rtrim($frontendUrl, '/') . "/reset-password?token={$token}&email=" . urlencode($user->email);

        $bannerUrl = config('app.url') . '/images/email-banner.png';
        $profilePictureUrl = $user->profile_picture ? asset('storage/' . $user->profile_picture) : null;

        try {
            Mail::to($user->email)->send(new UserCreatedMail($user, $resetUrl, $bannerUrl, $profilePictureUrl));
        } catch (\Exception $e) {
            \Log::error('User created but mail sending failed: ' . $e->getMessage());
            return $this->success($user, 'User created but email failed to send. Check logs.', 201);
        }

        return $this->success($user, 'User created successfully and email notification sent.', 201);
    }

    //update profile by id
    public function updateProfileById(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name'   => 'sometimes|string|max:255',
            'email'  => 'sometimes|string|email|unique:users,email,' . $user->id,
            'phone'  => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string|max:255',
            'profile_picture' => 'sometimes|nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);
        Log::info('Update payload', $data);
        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            $data['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        // Force assign values
        foreach ($data as $key => $value) {
            $user->{$key} = $value;
        }
        $user->save();

        $user->profile_picture_url = $user->profile_picture
            ? asset('storage/' . $user->profile_picture)
            : null;

        return $this->success(new UserResource($user), 'Profile updated successfully');
    }

    // Delete teacher by ID
    public function deleteTeacher($id)
    {
        $authUser = auth()->user();

        // Only allow certain roles to delete teachers
        if (!in_array($authUser->role, ['developer', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete teachers.',
            ], 403);
        }

        $teacher = User::where('id', $id)->where('role', 'teacher')->first();

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found.',
            ], 404);
        }

        try {
            // Delete profile picture from storage if exists
            if ($teacher->profile_picture && Storage::disk('public')->exists($teacher->profile_picture)) {
                Storage::disk('public')->delete($teacher->profile_picture);
            }

            $teacher->delete();

            return response()->json([
                'success' => true,
                'message' => 'Teacher deleted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete teacher: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete teacher. Please try again later.',
            ], 500);
        }
    }

    // Teacher resets a student's password
    public function resetStudentPassword(Request $request, $id)
    {
        $authUser = auth()->user();

        // Only allow teachers/admins
        if (!in_array($authUser->role, ['teacher', 'admin', 'developer'])) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to reset passwords.',
            ], 403);
        }

        $student = User::where('id', $id)->where('role', 'user')->first();
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.',
            ], 404);
        }

        // Validate password
        $data = $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $student->password = Hash::make($data['password']);
        $student->save();

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
        ]);
    }
}
