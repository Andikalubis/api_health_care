<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VitalSignService;
use App\Services\PatientDataService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class VitalSignController extends Controller
{
    protected $service;
    protected $patientService;

    public function __construct(VitalSignService $service, PatientDataService $patientService)
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
            $vitalSigns = $this->service->getAll($request, ['patient']);
            return $this->successResponse($vitalSigns, 'Berhasil mengambil daftar tanda-tanda vital.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar tanda-tanda vital.', 500);
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
                'blood_pressure' => 'required|string|max:20',
                'heart_rate' => 'required|integer',
                'body_temperature' => 'required|numeric',
                'breathing_rate' => 'required|integer',
                'oxygen_level' => 'required|integer',
                'check_time' => 'required|date',
            ]);

            $patient = $this->patientService->findById($validated['patient_id']);
            if (!$this->patientService->checkOwnership($patient, $request->user())) {
                return $this->errorResponse('Akses ditolak untuk menambahkan data pada pasien ini.', 403);
            }

            $vitalSign = $this->service->create($validated);
            return $this->successResponse($vitalSign, 'Berhasil membuat data tanda-tanda vital baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Pasien tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat data tanda-tanda vital.', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        try {
            $vitalSign = $this->service->findById($id);

            if (!$this->service->checkOwnership($vitalSign, $request->user())) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            return $this->successResponse($vitalSign->load('patient'), 'Berhasil mengambil detail data tanda-tanda vital.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Data tanda-tanda vital tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail data tanda-tanda vital.', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $vitalSign = $this->service->findById($id);

            if (!$this->service->checkOwnership($vitalSign, $request->user())) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $validated = $request->validate([
                'patient_id' => 'sometimes|required|exists:patient_data,id',
                'blood_pressure' => 'sometimes|required|string|max:20',
                'heart_rate' => 'sometimes|required|integer',
                'body_temperature' => 'sometimes|required|numeric',
                'breathing_rate' => 'sometimes|required|integer',
                'oxygen_level' => 'sometimes|required|integer',
                'check_time' => 'sometimes|required|date',
            ]);

            if (isset($validated['patient_id'])) {
                $patient = $this->patientService->findById($validated['patient_id']);
                if (!$this->patientService->checkOwnership($patient, $request->user())) {
                    return $this->errorResponse('Akses ditolak untuk memindahkan data ke pasien ini.', 403);
                }
            }

            $vitalSign = $this->service->update($id, $validated);
            return $this->successResponse($vitalSign, 'Berhasil mengubah data tanda-tanda vital.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Data tanda-tanda vital tidak ditemukan.', 404);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengubah data tanda-tanda vital.', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $vitalSign = $this->service->findById($id);

            if (!$this->service->checkOwnership($vitalSign, $request->user())) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $this->service->delete($id);
            return $this->successResponse(null, 'Berhasil menghapus data tanda-tanda vital.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Data tanda-tanda vital tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus data tanda-tanda vital.', 500);
        }
    }
}
