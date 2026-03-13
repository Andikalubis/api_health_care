<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicineHistory;
use App\Models\MedicineSchedule;
use Illuminate\Http\Request;

class MedicineHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = MedicineHistory::with('medicineSchedule');

        if ($user->role->value !== 'admin') {
            $query->whereHas('medicineSchedule.patient', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'schedule_id' => 'required|exists:medicine_schedules,id',
            'taken_time' => 'required|date',
            'status' => 'required|string|max:20',
        ]);

        if ($request->user()->role->value !== 'admin') {
            $schedule = MedicineSchedule::with('patient')->find($validated['schedule_id']);
            if ($schedule->patient->user_id !== $request->user()->id) {
                return response()->json(['message' => 'Unauthorized to add history for this schedule'], 403);
            }
        }

        $history = MedicineHistory::create($validated);
        return response()->json($history, 201);
    }

    public function show(Request $request, MedicineHistory $medicineHistory)
    {
        if ($request->user()->role->value !== 'admin' && $medicineHistory->medicineSchedule->patient->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        return response()->json($medicineHistory->load('medicineSchedule'));
    }

    public function update(Request $request, MedicineHistory $medicineHistory)
    {
        if ($request->user()->role->value !== 'admin' && $medicineHistory->medicineSchedule->patient->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'schedule_id' => 'sometimes|required|exists:medicine_schedules,id',
            'taken_time' => 'sometimes|required|date',
            'status' => 'sometimes|required|string|max:20',
        ]);

        if (isset($validated['schedule_id']) && $request->user()->role->value !== 'admin') {
            $schedule = MedicineSchedule::with('patient')->find($validated['schedule_id']);
            if ($schedule->patient->user_id !== $request->user()->id) {
                return response()->json(['message' => 'Unauthorized to move history to this schedule'], 403);
            }
        }

        $medicineHistory->update($validated);
        return response()->json($medicineHistory);
    }

    public function destroy(Request $request, MedicineHistory $medicineHistory)
    {
        if ($request->user()->role->value !== 'admin' && $medicineHistory->medicineSchedule->patient->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $medicineHistory->delete();
        return response()->json(null, 204);
    }
}
