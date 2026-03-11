<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MealType;
use Illuminate\Http\Request;

class MealTypeController extends Controller
{
    public function index()
    {
        return response()->json(MealType::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
        ]);

        $mealType = MealType::create($validated);
        return response()->json($mealType, 201);
    }

    public function show(MealType $mealType)
    {
        return response()->json($mealType);
    }

    public function update(Request $request, MealType $mealType)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:50',
        ]);

        $mealType->update($validated);
        return response()->json($mealType);
    }

    public function destroy(MealType $mealType)
    {
        $mealType->delete();
        return response()->json(null, 204);
    }
}
