<?php

namespace App\Http\Controllers;

use App\Attributes\ApiEndpoint;
use App\Attributes\ApiRequestBody;
use App\Attributes\ApiResponse;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    #[ApiEndpoint(summary: 'Register a new user', method: 'POST', path: '/api/auth/register', tags: ['Authentication'])]
    #[ApiRequestBody(properties: ['name' => 'string', 'email' => 'email', 'password' => 'string', 'password_confirmation' => 'string'], required: ['name', 'email', 'password', 'password_confirmation'])]
    #[ApiResponse(status: 201, description: 'User registered with token')]
    #[ApiResponse(status: 422, description: 'Validation error')]
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('auth')->plainTextToken,
        ], 201);
    }

    #[ApiEndpoint(summary: 'Login', method: 'POST', path: '/api/auth/login', tags: ['Authentication'])]
    #[ApiRequestBody(properties: ['email' => 'email', 'password' => 'string'], required: ['email', 'password'])]
    #[ApiResponse(status: 200, description: 'Authenticated with token')]
    #[ApiResponse(status: 401, description: 'Invalid credentials')]
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::firstWhere('email', $request->email);

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('auth')->plainTextToken,
        ]);
    }

    #[ApiEndpoint(summary: 'Logout', method: 'POST', path: '/api/auth/logout', tags: ['Authentication'])]
    #[ApiResponse(status: 200, description: 'Logged out successfully')]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    #[ApiEndpoint(summary: 'Request password reset', method: 'POST', path: '/api/auth/password/forgot', tags: ['Authentication'])]
    #[ApiRequestBody(properties: ['email' => 'email'], required: ['email'])]
    #[ApiResponse(status: 200, description: 'Reset token sent')]
    #[ApiResponse(status: 404, description: 'User not found')]
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $user = User::firstWhere('email', $request->email);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $token = Password::broker()->createToken($user);
        $user->notify(new ResetPasswordNotification($token));

        return response()->json(['message' => 'Password reset token sent successfully.']);
    }

    #[ApiEndpoint(summary: 'Reset password', method: 'POST', path: '/api/auth/password/reset', tags: ['Authentication'])]
    #[ApiRequestBody(properties: ['token' => 'string', 'email' => 'email', 'password' => 'string', 'password_confirmation' => 'string'], required: ['token', 'email', 'password', 'password_confirmation'])]
    #[ApiResponse(status: 200, description: 'Password reset successfully')]
    #[ApiResponse(status: 400, description: 'Invalid or expired token')]
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset($request->all(), function (User $user, string $password) {
            $user->update(['password' => Hash::make($password)]);
        });

        if ($status !== Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Invalid or expired token.'], 400);
        }

        return response()->json(['message' => 'Password reset successfully.']);
    }
}
