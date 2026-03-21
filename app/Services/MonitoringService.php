<?php

namespace App\Services;

use App\Models\PatientData;

class MonitoringService
{
    /**
     * Get all monitoring data for admin.
     */
    public function getAllMonitoringData()
    {
        return PatientData::with(['user', 'healthChecks', 'vitalSigns'])->get();
    }

    /**
     * Get monitoring data for a specific user.
     */
    public function getUserMonitoringData($userId)
    {
        return PatientData::where('user_id', $userId)
            ->with(['healthChecks', 'vitalSigns'])
            ->get();
    }
}
