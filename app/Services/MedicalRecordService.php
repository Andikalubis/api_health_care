<?php

namespace App\Services;

use App\Models\HealthCheck;
use App\Models\PatientData;
use App\Models\VitalSign;
use Illuminate\Support\Facades\DB;

class MedicalRecordService
{
    /**
     * Store combined medical record.
     */
    public function storeCombinedRecord(array $data)
    {
        return DB::transaction(function () use ($data) {
            $results = [];
            $patient_id = $data['patient_id'];
            $check_time = $data['check_time'];

            if (!empty($data['vital_signs'])) {
                $vitalSignData = array_merge($data['vital_signs'], [
                    'patient_id' => $patient_id,
                    'check_time' => $check_time,
                ]);
                $results['vital_signs'] = VitalSign::create($vitalSignData);
            }

            if (!empty($data['health_checks'])) {
                $results['health_checks'] = [];
                foreach ($data['health_checks'] as $check) {
                    $checkData = array_merge($check, [
                        'patient_id' => $patient_id,
                        'check_time' => $check_time,
                    ]);
                    $results['health_checks'][] = HealthCheck::create($checkData);
                }
            }

            return $results;
        });
    }

    /**
     * Get integrated medical records for a patient.
     */
    public function getIntegratedRecords($patientId)
    {
        $patient = PatientData::findOrFail($patientId);

        $vitalSigns = VitalSign::where('patient_id', $patient->id)
            ->orderBy('check_time', 'desc')
            ->get();

        $healthChecks = HealthCheck::with('healthType')
            ->where('patient_id', $patient->id)
            ->orderBy('check_time', 'desc')
            ->get();

        return [
            'patient' => $patient,
            'vital_signs' => $vitalSigns,
            'health_checks' => $healthChecks,
        ];
    }
}
