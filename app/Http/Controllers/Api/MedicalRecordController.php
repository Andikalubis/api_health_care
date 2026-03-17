<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthCheck;
use App\Models\PatientData;
use App\Models\VitalSign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class MedicalRecordController extends Controller
{
    /**
     * Store a combined medical record (Vital Signs and Health Checks).
     * Menyimpan rekam medis gabungan (Tanda-tanda Vital dan Cek Kesehatan).
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

            $user = $request->user();
            $patient = PatientData::findOrFail($validated['patient_id']);

            // Ownership check
            if ($user->role->value !== 'admin' && $patient->user_id !== $user->id) {
                return $this->errorResponse('Akses ditolak untuk menambahkan data pada pasien ini.', 403);
            }

            return DB::transaction(function () use ($validated, $patient) {
                $results = [];

                // 1. Save Vital Signs if provided
                if (!empty($validated['vital_signs'])) {
                    $vitalSignData = array_merge($validated['vital_signs'], [
                        'patient_id' => $patient->id,
                        'check_time' => $validated['check_time'],
                    ]);
                    $results['vital_signs'] = VitalSign::create($vitalSignData);
                }

                // 2. Save Health Checks if provided
                if (!empty($validated['health_checks'])) {
                    $results['health_checks'] = [];
                    foreach ($validated['health_checks'] as $check) {
                        $checkData = array_merge($check, [
                            'patient_id' => $patient->id,
                            'check_time' => $validated['check_time'],
                        ]);
                        $results['health_checks'][] = HealthCheck::create($checkData);
                    }
                }

                return $this->successResponse($results, 'Berhasil menyimpan rekam medis.', 201);
            });
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
     * Mengambil detail terintegrasi data medis untuk pasien.
     */
    public function show(Request $request, $patient_id)
    {
        try {
            $user = $request->user();
            $patient = PatientData::findOrFail($patient_id);

            // Ownership check
            if ($user->role->value !== 'admin' && $patient->user_id !== $user->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            // Fetch vital signs and health checks together
            $vitalSigns = VitalSign::where('patient_id', $patient->id)
                ->orderBy('check_time', 'desc')
                ->get();

            $healthChecks = HealthCheck::with('healthType')
                ->where('patient_id', $patient->id)
                ->orderBy('check_time', 'desc')
                ->get();

            return $this->successResponse([
                'patient' => $patient,
                'vital_signs' => $vitalSigns,
                'health_checks' => $healthChecks,
            ], 'Berhasil mengambil detail rekam medis terpadu.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Pasien tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail rekam medis.', 500);
        }
    }
}
