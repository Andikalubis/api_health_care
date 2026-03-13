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

    public function index(Request $request)
    {
        $user = $request->user();
        $query = HealthCheck::with(['patient', 'healthType']);

        if ($user->role->value !== 'admin') {
            $query->whereHas('patient', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        return response()->json($query->get());
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

        // Ownership check
        if ($request->user()->role->value !== 'admin') {
            $patient = PatientData::find($validated['patient_id']);
            if ($patient->user_id !== $request->user()->id) {
                return response()->json(['message' => 'Unauthorized to add data for this patient'], 403);
            }
        }

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

    public function show(Request $request, HealthCheck $healthCheck)
    {
        if ($request->user()->role->value !== 'admin' && $healthCheck->patient->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        return response()->json($healthCheck->load(['patient', 'healthType', 'healthAlerts']));
    }

    public function update(Request $request, HealthCheck $healthCheck)
    {
        if ($request->user()->role->value !== 'admin' && $healthCheck->patient->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'patient_id' => 'sometimes|required|exists:patient_data,id',
            'health_type_id' => 'sometimes|required|exists:health_types,id',
            'result_value' => 'sometimes|required|numeric',
            'status' => 'sometimes|required|string|max:20',
            'notes' => 'nullable|string',
            'check_time' => 'sometimes|required|date',
        ]);

        // If changing patient_id, check ownership of NEW patient
        if (isset($validated['patient_id']) && $request->user()->role->value !== 'admin') {
            $patient = PatientData::find($validated['patient_id']);
            if ($patient->user_id !== $request->user()->id) {
                return response()->json(['message' => 'Unauthorized to move data to this patient'], 403);
            }
        }

        $healthCheck->update($validated);
        return response()->json($healthCheck);
    }

    public function destroy(Request $request, HealthCheck $healthCheck)
    {
        if ($request->user()->role->value !== 'admin' && $healthCheck->patient->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $healthCheck->delete();
        return response()->json(null, 204);
    }
}