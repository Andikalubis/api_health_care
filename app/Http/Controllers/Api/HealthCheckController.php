<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthCheck;
use App\Models\PatientData;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class HealthCheckController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Display a listing of the resource.
     * Mengambil daftar hasil pemeriksaan kesehatan.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = HealthCheck::with(['patient', 'healthType']);

            if ($user->role->value !== 'admin') {
                $query->whereHas('patient', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }

            $healthChecks = $query->get();

            return $this->successResponse($healthChecks, 'Berhasil mengambil daftar pemeriksaan kesehatan.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar pemeriksaan kesehatan.', 500, $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     * Menyimpan hasil pemeriksaan kesehatan baru ke dalam database.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'patient_id' => 'required|exists:patient_data,id',
                'health_type_id' => 'required|exists:health_types,id',
                'result_value' => 'required|numeric',
                'status' => 'required|string|max:20',
                'notes' => 'nullable|string',
                'check_time' => 'required|date',
            ]);

            // Ownership check
            if ($request->user()->role->value !== 'admin') {
                $patient = PatientData::find($validated['patient_id']);
                if ($patient->user_id !== $request->user()->id) {
                    return $this->errorResponse('Akses ditolak untuk menambahkan data pada pasien ini.', 403);
                }
            }

            $healthCheck = HealthCheck::create($validated);
            $healthCheck->load('healthType', 'patient');

            // Automate Telegram Alert
            if (in_array($healthCheck->status, ['warning', 'danger'])) {
                try {
                    $this->telegramService->sendHealthAlert(
                        $healthCheck->patient->user_id,
                        $healthCheck->healthType->name,
                        $healthCheck->result_value,
                        $healthCheck->healthType->unit,
                        $healthCheck->status,
                        $healthCheck->notes ?? 'Segera periksa kondisi Anda.'
                    );
                } catch (\Exception $e) {
                    // Log or handle telegram failure silently without failing the main request
                }
            }

            return $this->successResponse($healthCheck, 'Berhasil membuat pemeriksaan kesehatan baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat pemeriksaan kesehatan.', 500, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     * Menampilkan detail dari hasil pemeriksaan kesehatan berdasarkan ID.
     */
    public function show(Request $request, $id)
    {
        try {
            $healthCheck = HealthCheck::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $healthCheck->patient->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            return $this->successResponse($healthCheck->load(['patient', 'healthType', 'healthAlerts']), 'Berhasil mengambil detail pemeriksaan kesehatan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Pemeriksaan kesehatan tidak ditemukan.', 404, $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail pemeriksaan kesehatan.', 500, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     * Mengubah data hasil pemeriksaan kesehatan yang sudah ada di database.
     */
    public function update(Request $request, $id)
    {
        try {
            $healthCheck = HealthCheck::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $healthCheck->patient->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $validated = $request->validate([
                'patient_id' => 'sometimes|required|exists:patient_data,id',
                'health_type_id' => 'sometimes|required|exists:health_types,id',
                'result_value' => 'sometimes|required|numeric',
                'status' => 'sometimes|required|string|max:20',
                'notes' => 'nullable|string',
                'check_time' => 'sometimes|required|date',
            ]);

            // If changing patient_id, check ownership of NEW patient
            if (isset($validated['patient_id']) && $request->user()->role->value !== 'admin') {
                $patient = PatientData::find($validated['patient_id']);
                if ($patient->user_id !== $request->user()->id) {
                    return $this->errorResponse('Akses ditolak untuk memindahkan data ke pasien ini.', 403);
                }
            }

            $healthCheck->update($validated);

            return $this->successResponse($healthCheck, 'Berhasil mengubah data pemeriksaan kesehatan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Pemeriksaan kesehatan tidak ditemukan.', 404, $e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengubah data pemeriksaan kesehatan.', 500, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     * Menghapus data hasil pemeriksaan kesehatan dari database.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $healthCheck = HealthCheck::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $healthCheck->patient->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $healthCheck->delete();

            return $this->successResponse(null, 'Berhasil menghapus pemeriksaan kesehatan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Pemeriksaan kesehatan tidak ditemukan.', 404, $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus pemeriksaan kesehatan.', 500, $e->getMessage());
        }
    }
}