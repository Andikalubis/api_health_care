<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicineHistory;
use Illuminate\Http\Request;

class MedicineHistoryController extends Controller
{
    public function index()
    {
        return response()->json(MedicineHistory::with('medicineSchedule')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'schedule_id' => 'required|exists:medicine_schedules,id',
            'taken_time' => 'required|date',
            'status' => 'required|string|max:20',
        ]);

        $history = MedicineHistory::create($validated);
        return response()->json($history, 201);
    }

    public function show(MedicineHistory $medicineHistory)
    {
        return response()->json($medicineHistory->load('medicineSchedule'));
    }

    public function update(Request $request, MedicineHistory $medicineHistory)
    {
        $validated = $request->validate([
            'schedule_id' => 'sometimes|required|exists:medicine_schedules,id',
            'taken_time' => 'sometimes|required|date',
            'status' => 'sometimes|required|string|max:20',
        ]);

        $medicineHistory->update($validated);
        return response()->json($medicineHistory);
    }

    public function destroy(MedicineHistory $medicineHistory)
    {
        $medicineHistory->delete();
        return response()->json(null, 204);
    }
}
