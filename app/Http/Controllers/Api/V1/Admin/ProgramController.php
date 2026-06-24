<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProgramController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $programs = Program::with('department.college')
            ->when($request->department_id, fn($q) => $q->where('department_id', $request->department_id))
            ->where('is_active', true)
            ->withCount(['subjects', 'sections'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $programs,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:programs,code',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $program = Program::create($validated);
        $program->load('department.college');

        return response()->json([
            'success' => true,
            'message' => 'Program created successfully.',
            'data' => $program,
        ], 201);
    }

    public function show(Program $program): JsonResponse
    {
        $program->load(['department.college', 'subjects', 'sections']);

        return response()->json([
            'success' => true,
            'data' => $program,
        ]);
    }

    public function update(Request $request, Program $program): JsonResponse
    {
        $validated = $request->validate([
            'department_id' => 'sometimes|exists:departments,id',
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:programs,code,' . $program->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $program->update($validated);
        $program->load('department.college');

        return response()->json([
            'success' => true,
            'message' => 'Program updated successfully.',
            'data' => $program,
        ]);
    }

    public function destroy(Program $program): JsonResponse
    {
        $program->delete();

        return response()->json([
            'success' => true,
            'message' => 'Program deleted successfully.',
        ]);
    }
}
