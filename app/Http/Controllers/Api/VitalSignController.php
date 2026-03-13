<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VitalSign;
use App\Models\PatientData;
use Illuminate\Http\Request;

class VitalSignController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = VitalSign::with('patient');

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
            'blood_pressure' => 'required|string|max:20',
            'heart_rate' => 'required|integer',
            'body_temperature' => 'required|numeric',
            'breathing_rate' => 'required|integer',
            'oxygen_level' => 'required|integer',
            'check_time' => 'required|date',
        ]);

        if ($request->user()->role->value !== 'admin') {
            $patient = PatientData::find($validated['patient_id']);
            if ($patient->user_id !== $request->user()->id) {
                return response()->json(['message' => 'Unauthorized to add data for this patient'], 403);
            }
        }

        $vitalSign = VitalSign::create($validated);
        return response()->json($vitalSign, 201);
    }

    public function show(Request $request, VitalSign $vitalSign)
    {
        if ($request->user()->role->value !== 'admin' && $vitalSign->patient->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        return response()->json($vitalSign->load('patient'));
    }

    public function update(Request $request, VitalSign $vitalSign)
    {
        if ($request->user()->role->value !== 'admin' && $vitalSign->patient->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'patient_id' => 'sometimes|required|exists:patient_data,id',
            'blood_pressure' => 'sometimes|required|string|max:20',
            'heart_rate' => 'sometimes|required|integer',
            'body_temperature' => 'sometimes|required|numeric',
            'breathing_rate' => 'sometimes|required|integer',
            'oxygen_level' => 'sometimes|required|integer',
            'check_time' => 'sometimes|required|date',
        ]);

        if (isset($validated['patient_id']) && $request->user()->role->value !== 'admin') {
            $patient = PatientData::find($validated['patient_id']);
            if ($patient->user_id !== $request->user()->id) {
                return response()->json(['message' => 'Unauthorized to move data to this patient'], 403);
            }
        }

        $vitalSign->update($validated);
        return response()->json($vitalSign);
    }

    public function destroy(Request $request, VitalSign $vitalSign)
    {
        if ($request->user()->role->value !== 'admin' && $vitalSign->patient->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $vitalSign->delete();
        return response()->json(null, 204);
    }
}
