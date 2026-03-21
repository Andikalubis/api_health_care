<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterNotification;
use App\Services\MasterNotificationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MasterNotificationController extends Controller
{
    protected $service;

    public function __construct(MasterNotificationService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $masterNotifications = $this->service->getAll($request);
            return $this->successResponse($masterNotifications, 'Berhasil mengambil daftar master notifikasi.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar master notifikasi.', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:100',
                'message' => 'required|string',
                'notification_type' => 'required|string|max:50',
            ]);

            $masterNotification = $this->service->create($validated);
            return $this->successResponse($masterNotification, 'Berhasil membuat master notifikasi baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat master notifikasi.', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $masterNotification = $this->service->findById($id);
            return $this->successResponse($masterNotification, 'Berhasil mengambil detail master notifikasi.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Master notifikasi tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail master notifikasi.', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'title' => 'sometimes|string|max:100',
                'message' => 'sometimes|string',
                'notification_type' => 'sometimes|string|max:50',
            ]);

            $masterNotification = $this->service->update($id, $validated);
            return $this->successResponse($masterNotification, 'Berhasil mengubah data master notifikasi.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Master notifikasi tidak ditemukan.', 404);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengubah data master notifikasi.', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $this->service->delete($id);
            return $this->successResponse(null, 'Berhasil menghapus master notifikasi.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Master notifikasi tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus master notifikasi.', 500);
        }
    }
}
