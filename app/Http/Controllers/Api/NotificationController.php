<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     * Mengambil daftar notifikasi.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = Notification::with('user');

            if ($user->role->value !== 'admin') {
                $query->where('user_id', $user->id);
            }

            $notifications = $query->get();

            return $this->successResponse($notifications, 'Berhasil mengambil daftar notifikasi.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar notifikasi.', 500, $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     * Menyimpan notifikasi baru ke dalam database.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'title' => 'required|string|max:100',
                'message' => 'required|string',
                'notification_type' => 'required|string|max:50',
                'send_time' => 'required|date',
            ]);

            if ($request->user()->role->value !== 'admin' && $validated['user_id'] != $request->user()->id) {
                return $this->errorResponse('Akses ditolak untuk mengirim notifikasi ke pengguna lain.', 403);
            }

            $notification = Notification::create($validated);

            return $this->successResponse($notification, 'Berhasil membuat notifikasi baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat notifikasi.', 500, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     * Menampilkan detail dari notifikasi berdasarkan ID.
     */
    public function show(Request $request, $id)
    {
        try {
            $notification = Notification::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $notification->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            return $this->successResponse($notification->load('user'), 'Berhasil mengambil detail notifikasi.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Notifikasi tidak ditemukan.', 404, $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail notifikasi.', 500, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     * Mengubah data notifikasi yang sudah ada di database.
     */
    public function update(Request $request, $id)
    {
        try {
            $notification = Notification::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $notification->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $validated = $request->validate([
                'user_id' => 'sometimes|required|exists:users,id',
                'title' => 'sometimes|required|string|max:100',
                'message' => 'sometimes|required|string',
                'notification_type' => 'sometimes|required|string|max:50',
                'send_time' => 'sometimes|required|date',
            ]);

            if (isset($validated['user_id']) && $request->user()->role->value !== 'admin' && $validated['user_id'] != $request->user()->id) {
                return $this->errorResponse('Akses ditolak untuk memindahkan notifikasi ke pengguna lain.', 403);
            }

            $notification->update($validated);

            return $this->successResponse($notification, 'Berhasil mengubah data notifikasi.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Notifikasi tidak ditemukan.', 404, $e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengubah data notifikasi.', 500, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     * Menghapus notifikasi dari database.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $notification = Notification::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $notification->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $notification->delete();

            return $this->successResponse(null, 'Berhasil menghapus notifikasi.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Notifikasi tidak ditemukan.', 404, $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus notifikasi.', 500, $e->getMessage());
        }
    }
}
