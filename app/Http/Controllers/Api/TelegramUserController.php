<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TelegramUser;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class TelegramUserController extends Controller
{
    /**
     * Display a listing of the resource.
     * Mengambil daftar pengguna telegram.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = TelegramUser::with('user');

            if ($user->role->value !== 'admin') {
                $query->where('user_id', $user->id);
            }

            $telegramUsers = $query->get();

            return $this->successResponse($telegramUsers, 'Berhasil mengambil daftar pengguna telegram.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar pengguna telegram.', 500, $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     * Menyimpan pengguna telegram baru ke dalam database.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'telegram_chat_id' => 'required|string|max:255',
                'telegram_username' => 'nullable|string|max:100',
            ]);

            if ($request->user()->role->value !== 'admin' && $validated['user_id'] != $request->user()->id) {
                return $this->errorResponse('Akses ditolak untuk mengatur telegram orang lain.', 403);
            }

            $telegramUser = TelegramUser::create($validated);

            return $this->successResponse($telegramUser, 'Berhasil membuat pengguna telegram baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat pengguna telegram.', 500, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     * Menampilkan detail dari pengguna telegram berdasarkan ID.
     */
    public function show(Request $request, $id)
    {
        try {
            $telegramUser = TelegramUser::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $telegramUser->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            return $this->successResponse($telegramUser->load('user'), 'Berhasil mengambil detail pengguna telegram.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Pengguna telegram tidak ditemukan.', 404, $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail pengguna telegram.', 500, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     * Mengubah data pengguna telegram yang sudah ada di database.
     */
    public function update(Request $request, $id)
    {
        try {
            $telegramUser = TelegramUser::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $telegramUser->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $validated = $request->validate([
                'user_id' => 'sometimes|required|exists:users,id',
                'telegram_chat_id' => 'sometimes|required|string|max:255',
                'telegram_username' => 'nullable|string|max:100',
            ]);

            if (isset($validated['user_id']) && $request->user()->role->value !== 'admin' && $validated['user_id'] != $request->user()->id) {
                return $this->errorResponse('Akses ditolak untuk memindahkan telegram ke orang lain.', 403);
            }

            $telegramUser->update($validated);

            return $this->successResponse($telegramUser, 'Berhasil mengubah data pengguna telegram.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Pengguna telegram tidak ditemukan.', 404, $e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengubah data pengguna telegram.', 500, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     * Menghapus pengguna telegram dari database.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $telegramUser = TelegramUser::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $telegramUser->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $telegramUser->delete();

            return $this->successResponse(null, 'Berhasil menghapus pengguna telegram.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Pengguna telegram tidak ditemukan.', 404, $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus pengguna telegram.', 500, $e->getMessage());
        }
    }
}
