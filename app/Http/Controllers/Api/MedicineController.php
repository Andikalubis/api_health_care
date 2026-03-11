<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    public function index()
    {
        return response()->json(Medicine::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $medicine = Medicine::create($validated);
        return response()->json($medicine, 201);
    }

    public function show(Medicine $medicine)
    {
        return response()->json($medicine);
    }

    public function update(Request $request, Medicine $medicine)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $medicine->update($validated);
        return response()->json($medicine);
    }

    public function destroy(Medicine $medicine)
    {
        $medicine->delete();
        return response()->json(null, 204);
    }
}
