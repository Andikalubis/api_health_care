<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TelegramUser;
use App\Services\TelegramUserService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class TelegramUserController extends Controller
{
    protected $service;

    public function __construct(TelegramUserService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $telegramUsers = $this->service->getAll($request, ['user']);
            return $this->successResponse($telegramUsers, 'Berhasil mengambil daftar pengguna telegram.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar pengguna telegram.', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'telegram_chat_id' => 'required|string|max:255',
                'telegram_username' => 'nullable|string|max:100',
            ]);

            if (!$request->user()->isAdmin() && $validated['user_id'] != $request->user()->id) {
                return $this->errorResponse('Akses ditolak untuk mengatur telegram orang lain.', 403);
            }

            $telegramUser = $this->service->create($validated);
            return $this->successResponse($telegramUser, 'Berhasil membuat pengguna telegram baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat pengguna telegram.', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        try {
            $telegramUser = $this->service->findById($id);

            if (!$this->service->checkOwnership($telegramUser, $request->user())) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            return $this->successResponse($telegramUser->load('user'), 'Berhasil mengambil detail pengguna telegram.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Pengguna telegram tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail pengguna telegram.', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $telegramUser = $this->service->findById($id);

            if (!$this->service->checkOwnership($telegramUser, $request->user())) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $validated = $request->validate([
                'user_id' => 'sometimes|required|exists:users,id',
                'telegram_chat_id' => 'sometimes|required|string|max:255',
                'telegram_username' => 'nullable|string|max:100',
            ]);

            if (isset($validated['user_id']) && !$request->user()->isAdmin() && $validated['user_id'] != $request->user()->id) {
                return $this->errorResponse('Akses ditolak untuk memindahkan telegram ke orang lain.', 403);
            }

            $telegramUser = $this->service->update($id, $validated);
            return $this->successResponse($telegramUser, 'Berhasil mengubah data pengguna telegram.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Pengguna telegram tidak ditemukan.', 404);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengubah data pengguna telegram.', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $telegramUser = $this->service->findById($id);

            if (!$this->service->checkOwnership($telegramUser, $request->user())) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $this->service->delete($id);
            return $this->successResponse(null, 'Berhasil menghapus pengguna telegram.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Pengguna telegram tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus pengguna telegram.', 500);
        }
    }
}
