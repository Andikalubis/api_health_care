<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthType;
use Illuminate\Http\Request;

class HealthTypeController extends Controller
{
    public function index()
    {
        return response()->json(HealthType::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'unit' => 'required|string|max:20',
            'normal_min' => 'required|numeric',
            'normal_max' => 'required|numeric',
            'description' => 'nullable|string',
        ]);

        $healthType = HealthType::create($validated);
        return response()->json($healthType, 201);
    }

    public function show(HealthType $healthType)
    {
        return response()->json($healthType);
    }

    public function update(Request $request, HealthType $healthType)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'unit' => 'sometimes|required|string|max:20',
            'normal_min' => 'sometimes|required|numeric',
            'normal_max' => 'sometimes|required|numeric',
            'description' => 'nullable|string',
        ]);

        $healthType->update($validated);
        return response()->json($healthType);
    }

    public function destroy(HealthType $healthType)
    {
        $healthType->delete();
        return response()->json(null, 204);
    }
}
