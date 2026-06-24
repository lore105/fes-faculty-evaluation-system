<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\College;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CollegeController extends Controller
{
    public function index(): JsonResponse
    {
        $colleges = College::where('is_active', true)
            ->withCount('departments')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $colleges,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:colleges,code',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $college = College::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'College created successfully.',
            'data' => $college,
        ], 201);
    }

    public function show(College $college): JsonResponse
    {
        $college->load('departments');

        return response()->json([
            'success' => true,
            'data' => $college,
        ]);
    }

    public function update(Request $request, College $college): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:colleges,code,' . $college->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $college->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'College updated successfully.',
            'data' => $college,
        ]);
    }

    public function destroy(College $college): JsonResponse
    {
        $college->delete();

        return response()->json([
            'success' => true,
            'message' => 'College deleted successfully.',
        ]);
    }
}
