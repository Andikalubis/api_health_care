<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PatientData;
use App\Services\MedicalRecordService;
use App\Services\PatientDataService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class MedicalRecordController extends Controller
{
    protected $service;
    protected $patientService;

    public function __construct(MedicalRecordService $service, PatientDataService $patientService)
    {
        $this->service = $service;
        $this->patientService = $patientService;
    }

    /**
     * Store a combined medical record (Vital Signs and Health Checks).
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'patient_id' => 'required|exists:patient_data,id',
                'check_time' => 'required|date',
                // Vital Signs (TTV)
                'vital_signs' => 'nullable|array',
                'vital_signs.blood_pressure' => 'required_with:vital_signs|string|max:20',
                'vital_signs.heart_rate' => 'required_with:vital_signs|integer',
                'vital_signs.body_temperature' => 'required_with:vital_signs|numeric',
                'vital_signs.breathing_rate' => 'required_with:vital_signs|integer',
                'vital_signs.oxygen_level' => 'required_with:vital_signs|integer',
                // Health Checks (Multiple items)
                'health_checks' => 'nullable|array',
                'health_checks.*.health_type_id' => 'required|exists:health_types,id',
                'health_checks.*.result_value' => 'required|numeric',
                'health_checks.*.status' => 'required|string|max:20',
                'health_checks.*.notes' => 'nullable|string',
            ]);

            $patient = $this->patientService->findById($validated['patient_id']);

            if (!$this->patientService->checkOwnership($patient, $request->user())) {
                return $this->errorResponse('Akses ditolak untuk menambahkan data pada pasien ini.', 403);
            }

            $results = $this->service->storeCombinedRecord($validated);

            return $this->successResponse($results, 'Berhasil menyimpan rekam medis.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Pasien tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menyimpan rekam medis.', 500);
        }
    }

    /**
     * Get integrated detailed medical data for a patient.
     */
    public function show(Request $request, $patient_id)
    {
        try {
            $patient = $this->patientService->findById($patient_id);

            if (!$this->patientService->checkOwnership($patient, $request->user())) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $results = $this->service->getIntegratedRecords($patient_id);

            return $this->successResponse($results, 'Berhasil mengambil detail rekam medis terpadu.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Pasien tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail rekam medis.', 500);
        }
    }
}
