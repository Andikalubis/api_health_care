<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VitalSign;
use Illuminate\Http\Request;

class VitalSignController extends Controller
{
    public function index()
    {
        return response()->json(VitalSign::with('patient')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_data,id',
            'blood_pressure' => 'required|string|max:20',
            'heart_rate' => 'required|integer',
            'body_temperature' => 'required|numeric',
            'breathing_rate' => 'required|integer',
            'oxygen_level' => 'required|integer',
            'check_time' => 'required|date',
        ]);

        $vitalSign = VitalSign::create($validated);
        return response()->json($vitalSign, 201);
    }

    public function show(VitalSign $vitalSign)
    {
        return response()->json($vitalSign->load('patient'));
    }

    public function update(Request $request, VitalSign $vitalSign)
    {
        $validated = $request->validate([
            'patient_id' => 'sometimes|required|exists:patient_data,id',
            'blood_pressure' => 'sometimes|required|string|max:20',
            'heart_rate' => 'sometimes|required|integer',
            'body_temperature' => 'sometimes|required|numeric',
            'breathing_rate' => 'sometimes|required|integer',
            'oxygen_level' => 'sometimes|required|integer',
            'check_time' => 'sometimes|required|date',
        ]);

        $vitalSign->update($validated);
        return response()->json($vitalSign);
    }

    public function destroy(VitalSign $vitalSign)
    {
        $vitalSign->delete();
        return response()->json(null, 204);
    }
}
