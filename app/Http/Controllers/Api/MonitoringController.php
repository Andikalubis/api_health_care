<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PatientData;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    /**
     * Admin monitoring: Get all user data and their health history.
     * Monitor admin: Mengambil semua data pengguna dan riwayat kesehatannya.
     */
    public function adminMonitor()
    {
        try {
            // Admin can see all patient data with their associated user and health checks
            $monitoringData = PatientData::with(['user', 'healthChecks', 'vitalSigns'])->get();

            return $this->successResponse($monitoringData, 'Berhasil mengambil semua data monitoring pengguna.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil data monitoring.', 500);
        }
    }

    /**
     * User monitoring: Get only the current user's data and health history.
     * Monitor pengguna: Mengambil hanya data pengguna saat ini dan riwayat kesehatannya.
     */
    public function userMonitor(Request $request)
    {
        try {
            $user = $request->user();

            // A user might have multiple patient profiles (e.g., family members), 
            // or just one. We'll fetch all patient data associated with this user.
            $monitoringData = PatientData::where('user_id', $user->id)
                ->with(['healthChecks', 'vitalSigns'])
                ->get();

            return $this->successResponse($monitoringData, 'Berhasil mengambil data monitoring pengguna.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil data monitoring.', 500);
        }
    }
}
