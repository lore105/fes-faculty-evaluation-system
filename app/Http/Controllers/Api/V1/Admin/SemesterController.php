<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SemesterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $semesters = Semester::with('academicYear')
            ->when($request->academic_year_id, fn($q) => $q->where('academic_year_id', $request->academic_year_id))
            ->orderByDesc('start_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $semesters,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'name' => 'required|string|max:255',
            'term' => 'required|in:1st,2nd,summer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
        ]);

        $semester = Semester::create($validated);
        $semester->load('academicYear');

        return response()->json([
            'success' => true,
            'message' => 'Semester created successfully.',
            'data' => $semester,
        ], 201);
    }

    public function show(Semester $semester): JsonResponse
    {
        $semester->load('academicYear');

        return response()->json([
            'success' => true,
            'data' => $semester,
        ]);
    }

    public function update(Request $request, Semester $semester): JsonResponse
    {
        $validated = $request->validate([
            'academic_year_id' => 'sometimes|exists:academic_years,id',
            'name' => 'sometimes|string|max:255',
            'term' => 'sometimes|in:1st,2nd,summer',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'is_active' => 'boolean',
        ]);

        $semester->update($validated);
        $semester->load('academicYear');

        return response()->json([
            'success' => true,
            'message' => 'Semester updated successfully.',
            'data' => $semester,
        ]);
    }

    public function destroy(Semester $semester): JsonResponse
    {
        $semester->delete();

        return response()->json([
            'success' => true,
            'message' => 'Semester deleted successfully.',
        ]);
    }
}
