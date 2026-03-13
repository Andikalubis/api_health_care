<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthLimit;
use Illuminate\Http\Request;

class HealthLimitController extends Controller
{
    public function index()
    {
        return response()->json(HealthLimit::with('healthType')->get());
    }

    public function store(Request $request)
    {
        if ($request->user()->role->value !== 'admin') {
            return response()->json(['message' => 'Only admins can set health limits'], 403);
        }

        $validated = $request->validate([
            'health_type_id' => 'required|exists:health_types,id',
            'warning_min' => 'required|numeric',
            'warning_max' => 'required|numeric',
            'danger_min' => 'required|numeric',
            'danger_max' => 'required|numeric',
        ]);

        $limit = HealthLimit::create($validated);
        return response()->json($limit, 201);
    }

    public function show(HealthLimit $healthLimit)
    {
        return response()->json($healthLimit->load('healthType'));
    }

    public function update(Request $request, HealthLimit $healthLimit)
    {
        if ($request->user()->role->value !== 'admin') {
            return response()->json(['message' => 'Only admins can update health limits'], 403);
        }

        $validated = $request->validate([
            'health_type_id' => 'sometimes|required|exists:health_types,id',
            'warning_min' => 'sometimes|required|numeric',
            'warning_max' => 'sometimes|required|numeric',
            'danger_min' => 'sometimes|required|numeric',
            'danger_max' => 'sometimes|required|numeric',
        ]);

        $healthLimit->update($validated);
        return response()->json($healthLimit);
    }

    public function destroy(Request $request, HealthLimit $healthLimit)
    {
        if ($request->user()->role->value !== 'admin') {
            return response()->json(['message' => 'Only admins can delete health limits'], 403);
        }

        $healthLimit->delete();
        return response()->json(null, 204);
    }
}
