<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicineSchedule;
use App\Models\PatientData;
use Illuminate\Http\Request;

class MedicineScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = MedicineSchedule::with(['patient', 'medicine']);

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
            'medicine_id' => 'required|exists:medicines,id',
            'dosage' => 'required|string|max:50',
            'drink_time' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        if ($request->user()->role->value !== 'admin') {
            $patient = PatientData::find($validated['patient_id']);
            if ($patient->user_id !== $request->user()->id) {
                return response()->json(['message' => 'Unauthorized to add data for this patient'], 403);
            }
        }

        $schedule = MedicineSchedule::create($validated);
        return response()->json($schedule, 201);
    }

    public function show(Request $request, MedicineSchedule $medicineSchedule)
    {
        if ($request->user()->role->value !== 'admin' && $medicineSchedule->patient->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        return response()->json($medicineSchedule->load(['patient', 'medicine', 'medicineHistories']));
    }

    public function update(Request $request, MedicineSchedule $medicineSchedule)
    {
        if ($request->user()->role->value !== 'admin' && $medicineSchedule->patient->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'patient_id' => 'sometimes|required|exists:patient_data,id',
            'medicine_id' => 'sometimes|required|exists:medicines,id',
            'dosage' => 'sometimes|required|string|max:50',
            'drink_time' => 'sometimes|required',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date',
            'notes' => 'nullable|string',
        ]);

        if (isset($validated['patient_id']) && $request->user()->role->value !== 'admin') {
            $patient = PatientData::find($validated['patient_id']);
            if ($patient->user_id !== $request->user()->id) {
                return response()->json(['message' => 'Unauthorized to move data to this patient'], 403);
            }
        }

        $medicineSchedule->update($validated);
        return response()->json($medicineSchedule);
    }

    public function destroy(Request $request, MedicineSchedule $medicineSchedule)
    {
        if ($request->user()->role->value !== 'admin' && $medicineSchedule->patient->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $medicineSchedule->delete();
        return response()->json(null, 204);
    }
}
