<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HealthCheckService;
use App\Services\PatientDataService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class HealthCheckController extends Controller
{
    protected $service;
    protected $patientService;

    public function __construct(HealthCheckService $service, PatientDataService $patientService)
    {
        $this->service = $service;
        $this->patientService = $patientService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $healthChecks = $this->service->getAll($request, ['patient', 'healthType']);
            return $this->successResponse($healthChecks, 'Berhasil mengambil daftar pemeriksaan kesehatan.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar pemeriksaan kesehatan.', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
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

            $patient = $this->patientService->findById($validated['patient_id']);
            if (!$this->patientService->checkOwnership($patient, $request->user())) {
                return $this->errorResponse('Akses ditolak untuk menambahkan data pada pasien ini.', 403);
            }

            $healthCheck = $this->service->create($validated);
            return $this->successResponse($healthCheck->load('healthType', 'patient'), 'Berhasil membuat pemeriksaan kesehatan baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Pasien tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat pemeriksaan kesehatan.', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        try {
            $healthCheck = $this->service->findById($id);

            if (!$this->service->checkOwnership($healthCheck, $request->user())) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            return $this->successResponse($healthCheck->load(['patient', 'healthType', 'healthAlerts']), 'Berhasil mengambil detail pemeriksaan kesehatan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Pemeriksaan kesehatan tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail pemeriksaan kesehatan.', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $healthCheck = $this->service->findById($id);

            if (!$this->service->checkOwnership($healthCheck, $request->user())) {
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

            if (isset($validated['patient_id'])) {
                $patient = $this->patientService->findById($validated['patient_id']);
                if (!$this->patientService->checkOwnership($patient, $request->user())) {
                    return $this->errorResponse('Akses ditolak untuk memindahkan data ke pasien ini.', 403);
                }
            }

            $healthCheck = $this->service->update($id, $validated);
            return $this->successResponse($healthCheck, 'Berhasil mengubah data pemeriksaan kesehatan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Pemeriksaan kesehatan tidak ditemukan.', 404);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengubah data pemeriksaan kesehatan.', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $healthCheck = $this->service->findById($id);

            if (!$this->service->checkOwnership($healthCheck, $request->user())) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $this->service->delete($id);
            return $this->successResponse(null, 'Berhasil menghapus pemeriksaan kesehatan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Pemeriksaan kesehatan tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus pemeriksaan kesehatan.', 500);
        }
    }
}