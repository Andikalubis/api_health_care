<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthAlert;
use App\Models\HealthCheck;
use Illuminate\Http\Request;

class HealthAlertController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = HealthAlert::with('healthCheck');

        if ($user->role->value !== 'admin') {
            $query->whereHas('healthCheck.patient', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'health_check_id' => 'required|exists:health_checks,id',
            'alert_level' => 'required|string|max:20',
            'message' => 'required|string',
            'sent_status' => 'boolean',
        ]);

        if ($request->user()->role->value !== 'admin') {
            $healthCheck = HealthCheck::with('patient')->find($validated['health_check_id']);
            if ($healthCheck->patient->user_id !== $request->user()->id) {
                return response()->json(['message' => 'Unauthorized to add alert for this health check'], 403);
            }
        }

        $alert = HealthAlert::create($validated);
        return response()->json($alert, 201);
    }

    public function show(Request $request, HealthAlert $healthAlert)
    {
        if ($request->user()->role->value !== 'admin' && $healthAlert->healthCheck->patient->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        return response()->json($healthAlert->load('healthCheck'));
    }

    public function update(Request $request, HealthAlert $healthAlert)
    {
        if ($request->user()->role->value !== 'admin' && $healthAlert->healthCheck->patient->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'health_check_id' => 'sometimes|required|exists:health_checks,id',
            'alert_level' => 'sometimes|required|string|max:20',
            'message' => 'sometimes|required|string',
            'sent_status' => 'boolean',
        ]);

        if (isset($validated['health_check_id']) && $request->user()->role->value !== 'admin') {
            $healthCheck = HealthCheck::with('patient')->find($validated['health_check_id']);
            if ($healthCheck->patient->user_id !== $request->user()->id) {
                return response()->json(['message' => 'Unauthorized to move alert to this health check'], 403);
            }
        }

        $healthAlert->update($validated);
        return response()->json($healthAlert);
    }

    public function destroy(Request $request, HealthAlert $healthAlert)
    {
        if ($request->user()->role->value !== 'admin' && $healthAlert->healthCheck->patient->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $healthAlert->delete();
        return response()->json(null, 204);
    }
}
