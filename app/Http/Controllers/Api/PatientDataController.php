<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PatientData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PatientDataController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = PatientData::with('user');

        if ($user->role->value !== 'admin') {
            $query->where('user_id', $user->id);
        }

        return response()->json($query->get());
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

    public function show(Request $request, PatientData $patientData)
    {
        if ($request->user()->role->value !== 'admin' && $patientData->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        return response()->json($patientData->load('user', 'healthChecks', 'vitalSigns', 'medicineSchedules', 'mealSchedules'));
    }

    public function update(Request $request, PatientData $patientData)
    {
        if ($request->user()->role->value !== 'admin' && $patientData->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

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

    public function destroy(Request $request, PatientData $patientData)
    {
        if ($request->user()->role->value !== 'admin' && $patientData->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $patientData->delete();
        return response()->json(null, 204);
    }
}
