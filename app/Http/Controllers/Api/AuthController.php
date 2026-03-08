<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|string|in:admin,user',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? Role::USER->value,
        ]);

        $accessToken = $user->createToken('access_token', ['*'], now()->addHour())->plainTextToken;
        $refreshToken = $user->createToken('refresh_token', ['issue-access-token'], now()->addDays(7))->plainTextToken;

        return response()->json([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $accessToken = $user->createToken('access_token', ['*'], now()->addHour())->plainTextToken;
        $refreshToken = $user->createToken('refresh_token', ['issue-access-token'], now()->addDays(7))->plainTextToken;

        return response()->json([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $token = \Laravel\Sanctum\PersonalAccessToken::findToken($request->refresh_token);

        if (!$token || $token->name !== 'refresh_token' || ($token->expires_at && $token->expires_at->isPast())) {
            return response()->json(['message' => 'Invalid or expired refresh token'], 401);
        }

        $user = $token->tokenable;
        $newAccessToken = $user->createToken('access_token', ['*'], now()->addHour())->plainTextToken;

        return response()->json([
            'access_token' => $newAccessToken,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }
}
