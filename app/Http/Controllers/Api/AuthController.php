<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /**
     * Register a newly created user.
     * Mendaftarkan pengguna baru.
     */
    public function register(Request $request)
    {
        try {
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

            return $this->successResponse([
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'Berhasil mendaftarkan pengguna baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat pendaftaran.', 500);
        }
    }

    /**
     * Authenticate a user and return tokens.
     * Autentikasi pengguna dan kembalikan token.
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            if (!Auth::attempt($request->only('email', 'password'))) {
                return $this->errorResponse('Email atau password salah.', 401);
            }

            $user = User::where('email', $request->email)->firstOrFail();
            $accessToken = $user->createToken('access_token', ['*'], now()->addHour())->plainTextToken;
            $refreshToken = $user->createToken('refresh_token', ['issue-access-token'], now()->addDays(7))->plainTextToken;

            return $this->successResponse([
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'Berhasil masuk.');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat masuk.', 500);
        }
    }

    /**
     * Log the user out (Invalidate the token).
     * Mengeluarkan pengguna (Membatalkan token).
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();

            return $this->successResponse(null, 'Berhasil keluar.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat keluar.', 500);
        }
    }

    /**
     * Refresh an access token.
     * Memperbarui token akses.
     */
    public function refresh(Request $request)
    {
        try {
            $request->validate([
                'refresh_token' => 'required|string',
            ]);

            $token = PersonalAccessToken::findToken($request->refresh_token);

            if (!$token || $token->name !== 'refresh_token' || ($token->expires_at && $token->expires_at->isPast())) {
                return $this->errorResponse('Refresh token tidak valid atau telah kedaluwarsa.', 401);
            }

            $user = $token->tokenable;
            $newAccessToken = $user->createToken('access_token', ['*'], now()->addHour())->plainTextToken;

            return $this->successResponse([
                'access_token' => $newAccessToken,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'Berhasil memperbarui token.');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat memperbarui token.', 500);
        }
    }
}
