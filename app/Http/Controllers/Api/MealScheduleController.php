<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MealSchedule;
use Illuminate\Http\Request;

class MealScheduleController extends Controller
{
    public function index()
    {
        return response()->json(MealSchedule::with(['patient', 'mealType'])->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_data,id',
            'meal_type_id' => 'required|exists:meal_types,id',
            'meal_time' => 'required',
            'notes' => 'nullable|string',
        ]);

        $schedule = MealSchedule::create($validated);
        return response()->json($schedule, 201);
    }

    public function show(MealSchedule $mealSchedule)
    {
        return response()->json($mealSchedule->load(['patient', 'mealType']));
    }

    public function update(Request $request, MealSchedule $mealSchedule)
    {
        $validated = $request->validate([
            'patient_id' => 'sometimes|required|exists:patient_data,id',
            'meal_type_id' => 'sometimes|required|exists:meal_types,id',
            'meal_time' => 'sometimes|required',
            'notes' => 'nullable|string',
        ]);

        $mealSchedule->update($validated);
        return response()->json($mealSchedule);
    }

    public function destroy(MealSchedule $mealSchedule)
    {
        $mealSchedule->delete();
        return response()->json(null, 204);
    }
}
