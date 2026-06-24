<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AcademicYearController extends Controller
{
    public function index(): JsonResponse
    {
        $academicYears = AcademicYear::withCount('semesters')
            ->orderByDesc('start_year')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $academicYears,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_year' => 'required|digits:4|integer',
            'end_year' => 'required|digits:4|integer|gt:start_year',
            'is_active' => 'boolean',
        ]);

        $academicYear = AcademicYear::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Academic year created successfully.',
            'data' => $academicYear,
        ], 201);
    }

    public function show(AcademicYear $academicYear): JsonResponse
    {
        $academicYear->load('semesters');

        return response()->json([
            'success' => true,
            'data' => $academicYear,
        ]);
    }

    public function update(Request $request, AcademicYear $academicYear): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'start_year' => 'sometimes|digits:4|integer',
            'end_year' => 'sometimes|digits:4|integer',
            'is_active' => 'boolean',
        ]);

        $academicYear->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Academic year updated successfully.',
            'data' => $academicYear,
        ]);
    }

    public function destroy(AcademicYear $academicYear): JsonResponse
    {
        $academicYear->delete();

        return response()->json([
            'success' => true,
            'message' => 'Academic year deleted successfully.',
        ]);
    }
}
