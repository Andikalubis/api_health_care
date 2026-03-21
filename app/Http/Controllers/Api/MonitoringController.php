<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MonitoringService;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    protected $service;

    public function __construct(MonitoringService $service)
    {
        $this->service = $service;
    }

    /**
     * Admin monitoring: Get all user data and their health history.
     */
    public function adminMonitor()
    {
        try {
            $monitoringData = $this->service->getAllMonitoringData();
            return $this->successResponse($monitoringData, 'Berhasil mengambil semua data monitoring pengguna.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil data monitoring.', 500);
        }
    }

    /**
     * User monitoring: Get only the current user's data and health history.
     */
    public function userMonitor(Request $request)
    {
        try {
            $monitoringData = $this->service->getUserMonitoringData($request->user()->id);
            return $this->successResponse($monitoringData, 'Berhasil mengambil data monitoring pengguna.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil data monitoring.', 500);
        }
    }
}
