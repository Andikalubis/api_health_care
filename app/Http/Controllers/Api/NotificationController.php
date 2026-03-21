<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class NotificationController extends Controller
{
    protected $service;

    public function __construct(NotificationService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $notifications = $this->service->getAll($request, ['user']);
            return $this->successResponse($notifications, 'Berhasil mengambil daftar notifikasi.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar notifikasi.', 500);
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
                'title' => 'required|string|max:100',
                'message' => 'required|string',
                'notification_type' => 'required|string|max:50',
                'send_time' => 'required|date',
            ]);

            if (!$request->user()->isAdmin() && $validated['user_id'] != $request->user()->id) {
                return $this->errorResponse('Akses ditolak untuk mengirim notifikasi ke pengguna lain.', 403);
            }

            $notification = $this->service->create($validated);
            return $this->successResponse($notification, 'Berhasil membuat notifikasi baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat notifikasi.', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        try {
            $notification = $this->service->findById($id);

            if (!$this->service->checkOwnership($notification, $request->user())) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            return $this->successResponse($notification->load('user'), 'Berhasil mengambil detail notifikasi.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Notifikasi tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail notifikasi.', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $notification = $this->service->findById($id);

            if (!$this->service->checkOwnership($notification, $request->user())) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $validated = $request->validate([
                'user_id' => 'sometimes|required|exists:users,id',
                'title' => 'sometimes|required|string|max:100',
                'message' => 'sometimes|required|string',
                'notification_type' => 'sometimes|required|string|max:50',
                'send_time' => 'sometimes|required|date',
            ]);

            if (isset($validated['user_id']) && !$request->user()->isAdmin() && $validated['user_id'] != $request->user()->id) {
                return $this->errorResponse('Akses ditolak untuk memindahkan notifikasi ke pengguna lain.', 403);
            }

            $notification = $this->service->update($id, $validated);
            return $this->successResponse($notification, 'Berhasil mengubah data notifikasi.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Notifikasi tidak ditemukan.', 404);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengubah data notifikasi.', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $notification = $this->service->findById($id);

            if (!$this->service->checkOwnership($notification, $request->user())) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $this->service->delete($id);
            return $this->successResponse(null, 'Berhasil menghapus notifikasi.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Notifikasi tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus notifikasi.', 500);
        }
    }
}
