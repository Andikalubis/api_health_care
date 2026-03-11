<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PatientData;
use Illuminate\Http\Request;

class PatientDataController extends Controller
{
    public function index()
    {
        return response()->json(PatientData::with('user')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:100',
            'gender' => 'required|string|max:10',
            'birth_date' => 'required|date',
            'height' => 'required|numeric',
            'weight' => 'required|numeric',
            'blood_type' => 'required|string|max:5',
        ]);

        $patientData = PatientData::create($validated);
        return response()->json($patientData, 201);
    }

    public function show(PatientData $patientData)
    {
        return response()->json($patientData->load('user', 'healthChecks', 'vitalSigns', 'medicineSchedules', 'mealSchedules'));
    }

    public function update(Request $request, PatientData $patientData)
    {
        $validated = $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'name' => 'sometimes|required|string|max:100',
            'gender' => 'sometimes|required|string|max:10',
            'birth_date' => 'sometimes|required|date',
            'height' => 'sometimes|required|numeric',
            'weight' => 'sometimes|required|numeric',
            'blood_type' => 'sometimes|required|string|max:5',
        ]);

        $patientData->update($validated);
        return response()->json($patientData);
    }

    public function destroy(PatientData $patientData)
    {
        $patientData->delete();
        return response()->json(null, 204);
    }
}
