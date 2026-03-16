<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterNotification;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MasterNotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     * Mengambil semua daftar master notification.
     */
    public function index()
    {
        try {
            $masterNotifications = MasterNotification::all();
            return $this->successResponse($masterNotifications, 'Berhasil mengambil daftar master notifikasi.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar master notifikasi.', 500, $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     * Menyimpan master notification baru ke dalam database.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:100',
                'message' => 'required|string',
                'notification_type' => 'required|string|max:50',
            ]);

            $masterNotification = MasterNotification::create($validated);

            return $this->successResponse($masterNotification, 'Berhasil membuat master notifikasi baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat master notifikasi.', 500, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     * Menampilkan detail dari satu master notification berdasarkan ID.
     */
    public function show($id)
    {
        try {
            $masterNotification = MasterNotification::findOrFail($id);

            return $this->successResponse($masterNotification, 'Berhasil mengambil detail master notifikasi.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Master notifikasi tidak ditemukan.', 404, $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail master notifikasi.', 500, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     * Mengubah data master notification yang sudah ada di database.
     */
    public function update(Request $request, $id)
    {
        try {
            $masterNotification = MasterNotification::findOrFail($id);

            $validated = $request->validate([
                'title' => 'sometimes|string|max:100',
                'message' => 'sometimes|string',
                'notification_type' => 'sometimes|string|max:50',
            ]);

            $masterNotification->update($validated);

            return $this->successResponse($masterNotification, 'Berhasil mengubah data master notifikasi.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Master notifikasi tidak ditemukan.', 404, $e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengubah data master notifikasi.', 500, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     * Menghapus master notification dari database.
     */
    public function destroy($id)
    {
        try {
            $masterNotification = MasterNotification::findOrFail($id);
            $masterNotification->delete();

            return $this->successResponse(null, 'Berhasil menghapus master notifikasi.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Master notifikasi tidak ditemukan.', 404, $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus master notifikasi.', 500, $e->getMessage());
        }
    }
}
