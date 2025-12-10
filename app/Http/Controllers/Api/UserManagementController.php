<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\UserCreatedMail;
use App\Traits\ApiResponse;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserManagementController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 100);
        $authUser = auth()->user();

        // Only users who can have students
        if (in_array($authUser->role, ['teacher', 'developer'])) {
            // Use the students() relationship
            $users = $authUser->students()
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return $this->paginated($users, 'Users fetched successfully');
        }

        // Other roles → forbidden
        return response()->json([
            'success' => false,
            'message' => 'You do not have permission to view users.',
        ], 403);
    }

    public function notMyStudent(Request $request)
    {
        $perPage = $request->query('per_page', 100);
        $authUser = auth()->user();

        // Only users who can have students
        if (in_array($authUser->role, ['teacher', 'developer'])) {
            // Get IDs of the teacher/developer's students
            $myStudentIds = $authUser->students()->pluck('users.id');

            // Fetch users who are NOT their students, and exclude teachers/developers
            $users = User::whereNotIn('id', $myStudentIds)
                ->where('id', '!=', $authUser->id) // exclude self
                ->whereNotIn('role', ['developer', 'teacher', 'administrator']) // exclude specific roles
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);


            return $this->paginated($users, 'Users not under your students fetched successfully');
        }

        // Other roles → forbidden
        return response()->json([
            'success' => false,
            'message' => 'You do not have permission to view users.',
        ], 403);
    }




    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:user,administrator,deveveloper',
        ]);

        $user = User::findOrFail($id);
        $user->role = $request->role;
        $user->save();

        return $this->success($user, 'User role updated successfully');
    }

    //working without who add the accounts of users
    // public function store(Request $request)
    // {
    //     $data = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|email|unique:users',
    //         'password' => 'nullable|string|min:8', // optional: you can create with initial password or null
    //         'role' => 'required|in:user,administrator,dev',
    //         'phone' => 'nullable|string|max:20',
    //         'address' => 'nullable|string|max:255',
    //         'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    //     ]);

    //     // Handle profile picture
    //     $profilePicturePath = null;
    //     if ($request->hasFile('profile_picture')) {
    //         $profilePicturePath = $request->file('profile_picture')->store('profile_pictures', 'public');
    //     }

    //     $user = User::create([
    //         'name' => $data['name'],
    //         'email' => $data['email'],
    //         'password' => isset($data['password']) ? Hash::make($data['password']) : Hash::make(bin2hex(random_bytes(8))), // random if not provided
    //         'role' => $data['role'],
    //         'phone' => $data['phone'] ?? null,
    //         'address' => $data['address'] ?? null,
    //         'profile_picture' => $profilePicturePath,
    //     ]);

    //     // Create password reset token so user can set their password
    //     $token = Password::broker()->createToken($user);
    //     $frontendUrl = config('app.frontend_url', config('app.url'));
    //     $resetUrl = rtrim($frontendUrl, '/') . "/reset-password?token={$token}&email=" . urlencode($user->email);

    //     // Add banner URL and resolved profile picture URL to mail data (banner pick from public/images)
    //     $bannerUrl = config('app.url') . '/images/email-banner.png'; // put your banner at public/images/email-banner.png
    //     $profilePictureUrl = $user->profile_picture ? asset('storage/' . $user->profile_picture) : null;

    //     try {
    //         Mail::to($user->email)->send(new UserCreatedMail($user, $resetUrl, $bannerUrl, $profilePictureUrl));
    //     } catch (\Exception $e) {
    //         // If mail fails, you may log and still return success with a warning
    //         \Log::error('User created but mail sending failed: ' . $e->getMessage());
    //         return $this->success($user, 'User created but email failed to send. Check logs.', 201);
    //     }

    //     return $this->success($user, 'User created successfully and email notification sent.', 201);
    // }

    public function assignStudents(Request $request)
    {
        $authUser = auth()->user();

        if (!in_array($authUser->role, ['teacher', 'developer'])) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to assign students.',
            ], 403);
        }

        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
        ]);

        // Attach or sync students to the teacher/developer
        $authUser->students()->syncWithoutDetaching($request->student_ids);

        return response()->json([
            'success' => true,
            'message' => 'Students assigned successfully.',
        ]);
    }

    //updated using realationship table
    public function store(Request $request)
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

        // 🔑 If the user is a student, attach them to the teacher
        if ($user->role === 'user' && isset($data['teacher_id'])) {
            $teacher = User::find($data['teacher_id']);
            if ($teacher) {
                // Use Eloquent relationship to insert into relationship_table
                $teacher->students()->attach($user->id);
            }
        }

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

    // reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|string|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return $this->success(null, 'Password reset successfully');
        } else {
            return $this->error(
                'Reset unsuccessful, please contact administrator',
                400,
                ['status' => __($status)] // put translated status here
            );
        }
    }

    //update profile
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name'   => 'sometimes|string|max:255',
            'phone'  => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string|max:255',
            'profile_picture' => 'sometimes|nullable|image|mimes:jpg,jpeg,png',
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

        return $this->success(new UserResource(auth()->user()), 'Profile updated successfully');
    }



    public function getStudentById(Request $request, $id)
    {
        $user = User::findOrFail($id);
        return $this->success(new UserResource($user), 'User profile fetched successfully');
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
}
