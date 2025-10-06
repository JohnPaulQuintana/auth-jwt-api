<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return $this->success(new UserResource($user), 'User registered successfully', 201);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (!$token = auth()->attempt($credentials)) {
            return $this->error('Invalid email or password', 401);
        }

        return $this->success([
            'user' => new UserResource(auth()->user()),
            'authorisation' => [
                'token' => $token,
                'type'  => 'bearer',
            ],
        ], 'User logged in successfully');
    }

    public function profile()
    {
        return $this->success(new UserResource(auth()->user()), 'Profile fetched successfully');
    }

    public function refreshToken()
    {
        try {
            $newToken = auth()->refresh();

            // Re-login with new token to get user
            $user = auth()->setToken($newToken)->user();

            return $this->success([
                'user' => new UserResource($user),
                'authorisation' => [
                    'token' => $newToken,
                    'type'  => 'bearer',
                ],
            ], 'Token refreshed successfully');

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return $this->error('Could not refresh token', 401);
        }
    }


    public function logout()
    {
        auth()->logout();
        return $this->success(null, 'User logged out successfully');
    }
}
