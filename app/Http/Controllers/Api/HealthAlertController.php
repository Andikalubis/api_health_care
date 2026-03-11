<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthAlert;
use Illuminate\Http\Request;

class HealthAlertController extends Controller
{
    public function index()
    {
        return response()->json(HealthAlert::with('healthCheck')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'health_check_id' => 'required|exists:health_checks,id',
            'alert_level' => 'required|string|max:20',
            'message' => 'required|string',
            'sent_status' => 'boolean',
        ]);

        $alert = HealthAlert::create($validated);
        return response()->json($alert, 201);
    }

    public function show(HealthAlert $healthAlert)
    {
        return response()->json($healthAlert->load('healthCheck'));
    }

    public function update(Request $request, HealthAlert $healthAlert)
    {
        $validated = $request->validate([
            'health_check_id' => 'sometimes|required|exists:health_checks,id',
            'alert_level' => 'sometimes|required|string|max:20',
            'message' => 'sometimes|required|string',
            'sent_status' => 'boolean',
        ]);

        $healthAlert->update($validated);
        return response()->json($healthAlert);
    }

    public function destroy(HealthAlert $healthAlert)
    {
        $healthAlert->delete();
        return response()->json(null, 204);
    }
}
