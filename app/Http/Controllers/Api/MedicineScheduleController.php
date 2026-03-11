<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicineSchedule;
use Illuminate\Http\Request;

class MedicineScheduleController extends Controller
{
    public function index()
    {
        return response()->json(MedicineSchedule::with(['patient', 'medicine'])->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patient_data,id',
            'medicine_id' => 'required|exists:medicines,id',
            'dosage' => 'required|string|max:50',
            'drink_time' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $schedule = MedicineSchedule::create($validated);
        return response()->json($schedule, 201);
    }

    public function show(MedicineSchedule $medicineSchedule)
    {
        return response()->json($medicineSchedule->load(['patient', 'medicine', 'medicineHistories']));
    }

    public function update(Request $request, MedicineSchedule $medicineSchedule)
    {
        $validated = $request->validate([
            'patient_id' => 'sometimes|required|exists:patient_data,id',
            'medicine_id' => 'sometimes|required|exists:medicines,id',
            'dosage' => 'sometimes|required|string|max:50',
            'drink_time' => 'sometimes|required',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date',
            'notes' => 'nullable|string',
        ]);

        $medicineSchedule->update($validated);
        return response()->json($medicineSchedule);
    }

    public function destroy(MedicineSchedule $medicineSchedule)
    {
        $medicineSchedule->delete();
        return response()->json(null, 204);
    }
}
