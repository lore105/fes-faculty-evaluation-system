<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sections = Section::with('program.department.college')
            ->when($request->program_id, fn($q) => $q->where('program_id', $request->program_id))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sections,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'program_id' => 'required|exists:programs,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:sections,code',
            'capacity' => 'integer|min:1|max:100',
            'is_active' => 'boolean',
        ]);

        $section = Section::create($validated);
        $section->load('program.department.college');

        return response()->json([
            'success' => true,
            'message' => 'Section created successfully.',
            'data' => $section,
        ], 201);
    }

    public function show(Section $section): JsonResponse
    {
        $section->load('program.department.college');

        return response()->json([
            'success' => true,
            'data' => $section,
        ]);
    }

    public function update(Request $request, Section $section): JsonResponse
    {
        $validated = $request->validate([
            'program_id' => 'sometimes|exists:programs,id',
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:sections,code,' . $section->id,
            'capacity' => 'integer|min:1|max:100',
            'is_active' => 'boolean',
        ]);

        $section->update($validated);
        $section->load('program.department.college');

        return response()->json([
            'success' => true,
            'message' => 'Section updated successfully.',
            'data' => $section,
        ]);
    }

    public function destroy(Section $section): JsonResponse
    {
        $section->delete();

        return response()->json([
            'success' => true,
            'message' => 'Section deleted successfully.',
        ]);
    }
}
