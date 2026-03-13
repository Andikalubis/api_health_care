<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MealSchedule;
use App\Models\PatientData;
use Illuminate\Http\Request;

class MealScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = MealSchedule::with(['patient', 'mealType']);

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
            'meal_type_id' => 'required|exists:meal_types,id',
            'meal_time' => 'required',
            'notes' => 'nullable|string',
        ]);

        if ($request->user()->role->value !== 'admin') {
            $patient = PatientData::find($validated['patient_id']);
            if ($patient->user_id !== $request->user()->id) {
                return response()->json(['message' => 'Unauthorized to add data for this patient'], 403);
            }
        }

        $schedule = MealSchedule::create($validated);
        return response()->json($schedule, 201);
    }

    public function show(Request $request, MealSchedule $mealSchedule)
    {
        if ($request->user()->role->value !== 'admin' && $mealSchedule->patient->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        return response()->json($mealSchedule->load(['patient', 'mealType']));
    }

    public function update(Request $request, MealSchedule $mealSchedule)
    {
        if ($request->user()->role->value !== 'admin' && $mealSchedule->patient->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'patient_id' => 'sometimes|required|exists:patient_data,id',
            'meal_type_id' => 'sometimes|required|exists:meal_types,id',
            'meal_time' => 'sometimes|required',
            'notes' => 'nullable|string',
        ]);

        if (isset($validated['patient_id']) && $request->user()->role->value !== 'admin') {
            $patient = PatientData::find($validated['patient_id']);
            if ($patient->user_id !== $request->user()->id) {
                return response()->json(['message' => 'Unauthorized to move data to this patient'], 403);
            }
        }

        $mealSchedule->update($validated);
        return response()->json($mealSchedule);
    }

    public function destroy(Request $request, MealSchedule $mealSchedule)
    {
        if ($request->user()->role->value !== 'admin' && $mealSchedule->patient->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $mealSchedule->delete();
        return response()->json(null, 204);
    }
}
