<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthAlert;
use App\Models\HealthCheck;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class HealthAlertController extends Controller
{
    /**
     * Display a listing of the resource.
     * Mengambil daftar peringatan kesehatan.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = HealthAlert::with('healthCheck');

            if ($user->role->value !== 'admin') {
                $query->whereHas('healthCheck.patient', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }

            $alerts = $query->get();

            return $this->successResponse($alerts, 'Berhasil mengambil daftar peringatan kesehatan.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar peringatan kesehatan.', 500, $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     * Menyimpan peringatan kesehatan baru ke dalam database.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'health_check_id' => 'required|exists:health_checks,id',
                'alert_level' => 'required|string|max:20',
                'message' => 'required|string',
                'sent_status' => 'boolean',
            ]);

            if ($request->user()->role->value !== 'admin') {
                $healthCheck = HealthCheck::with('patient')->find($validated['health_check_id']);
                if ($healthCheck && $healthCheck->patient->user_id !== $request->user()->id) {
                    return $this->errorResponse('Akses ditolak untuk menambahkan peringatan pada cek kesehatan ini.', 403);
                }
            }

            $alert = HealthAlert::create($validated);

            return $this->successResponse($alert, 'Berhasil membuat peringatan kesehatan baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat peringatan kesehatan.', 500, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     * Menampilkan detail dari peringatan kesehatan berdasarkan ID.
     */
    public function show(Request $request, $id)
    {
        try {
            $healthAlert = HealthAlert::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $healthAlert->healthCheck->patient->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            return $this->successResponse($healthAlert->load('healthCheck'), 'Berhasil mengambil detail peringatan kesehatan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Peringatan kesehatan tidak ditemukan.', 404, $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail peringatan kesehatan.', 500, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     * Mengubah data peringatan kesehatan yang sudah ada di database.
     */
    public function update(Request $request, $id)
    {
        try {
            $healthAlert = HealthAlert::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $healthAlert->healthCheck->patient->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $validated = $request->validate([
                'health_check_id' => 'sometimes|required|exists:health_checks,id',
                'alert_level' => 'sometimes|required|string|max:20',
                'message' => 'sometimes|required|string',
                'sent_status' => 'boolean',
            ]);

            if (isset($validated['health_check_id']) && $request->user()->role->value !== 'admin') {
                $healthCheck = HealthCheck::with('patient')->find($validated['health_check_id']);
                if ($healthCheck && $healthCheck->patient->user_id !== $request->user()->id) {
                    return $this->errorResponse('Akses ditolak untuk memindahkan peringatan ke cek kesehatan ini.', 403);
                }
            }

            $healthAlert->update($validated);

            return $this->successResponse($healthAlert, 'Berhasil mengubah data peringatan kesehatan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Peringatan kesehatan tidak ditemukan.', 404, $e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengubah data peringatan kesehatan.', 500, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     * Menghapus peringatan kesehatan dari database.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $healthAlert = HealthAlert::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $healthAlert->healthCheck->patient->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $healthAlert->delete();

            return $this->successResponse(null, 'Berhasil menghapus peringatan kesehatan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Peringatan kesehatan tidak ditemukan.', 404, $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus peringatan kesehatan.', 500, $e->getMessage());
        }
    }
}
