<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthCheck;
use App\Models\PatientData;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class HealthCheckController extends Controller
{
    protected $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function index()
    {
        return response()->json(HealthCheck::with(['patient', 'healthType'])->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_data,id',
            'health_type_id' => 'required|exists:health_types,id',
            'result_value' => 'required|numeric',
            'status' => 'required|string|max:20',
            'notes' => 'nullable|string',
            'check_time' => 'required|date',
        ]);

        $healthCheck = HealthCheck::create($validated);
        $healthCheck->load('healthType', 'patient');

        // Automate Telegram Alert
        if (in_array($healthCheck->status, ['warning', 'danger'])) {
            $this->telegramService->sendHealthAlert(
                $healthCheck->patient->user_id,
                $healthCheck->healthType->name,
                $healthCheck->result_value,
                $healthCheck->healthType->unit,
                $healthCheck->status,
                $healthCheck->notes ?? 'Segera periksa kondisi Anda.'
            );
        }

        return response()->json($healthCheck, 201);
    }

    public function show(HealthCheck $healthCheck)
    {
        return response()->json($healthCheck->load(['patient', 'healthType', 'healthAlerts']));
    }

    public function update(Request $request, HealthCheck $healthCheck)
    {
        $validated = $request->validate([
            'patient_id' => 'sometimes|required|exists:patient_data,id',
            'health_type_id' => 'sometimes|required|exists:health_types,id',
            'result_value' => 'sometimes|required|numeric',
            'status' => 'sometimes|required|string|max:20',
            'notes' => 'nullable|string',
            'check_time' => 'sometimes|required|date',
        ]);

        $healthCheck->update($validated);
        return response()->json($healthCheck);
    }

    public function destroy(HealthCheck $healthCheck)
    {
        $healthCheck->delete();
        return response()->json(null, 204);
    }
}