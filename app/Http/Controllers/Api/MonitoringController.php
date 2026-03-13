<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PatientData;
use App\Models\User;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    /**
     * Admin monitoring: Get all user data and their health history.
     */
    public function adminMonitor()
    {
        // Admin can see all patient data with their associated user and health checks
        $monitoringData = PatientData::with(['user', 'healthChecks', 'vitalSigns'])->get();

        return response()->json([
            'status' => 'success',
            'message' => 'All user monitoring data retrieved successfully',
            'data' => $monitoringData
        ]);
    }

    /**
     * User monitoring: Get only the current user's data and health history.
     */
    public function userMonitor(Request $request)
    {
        $user = $request->user();

        // A user might have multiple patient profiles (e.g., family members), 
        // or just one. We'll fetch all patient data associated with this user.
        $monitoringData = PatientData::where('user_id', $user->id)
            ->with(['healthChecks', 'vitalSigns'])
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'User monitoring data retrieved successfully',
            'data' => $monitoringData
        ]);
    }
}
