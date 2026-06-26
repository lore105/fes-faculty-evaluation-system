<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentSection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentSectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $studentSections = StudentSection::with([
                'student:id,first_name,last_name,student_id',
                'section:id,name,code',
                'semester:id,name,term',
            ])
            ->when($request->semester_id, fn($q) => $q->where('semester_id', $request->semester_id))
            ->when($request->section_id, fn($q) => $q->where('section_id', $request->section_id))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $studentSections,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'section_id' => 'required|exists:sections,id',
            'semester_id' => 'required|exists:semesters,id',
            'status' => 'in:active,inactive',
        ]);

        // Validate student role
        $student = User::findOrFail($validated['user_id']);
        if (!$student->hasRole('student')) {
            return response()->json([
                'success' => false,
                'message' => 'User is not a student.',
            ], 422);
        }

        // Check duplicate
        $existing = StudentSection::where('user_id', $validated['user_id'])
            ->where('section_id', $validated['section_id'])
            ->where('semester_id', $validated['semester_id'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Student is already assigned to this section for this semester.',
            ], 422);
        }

        $studentSection = StudentSection::create($validated);
        $studentSection->load([
            'student:id,first_name,last_name,student_id',
            'section:id,name,code',
            'semester:id,name,term',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Student assigned to section successfully.',
            'data' => $studentSection,
        ], 201);
    }

    public function show(StudentSection $studentSection): JsonResponse
    {
        $studentSection->load([
            'student:id,first_name,last_name,student_id',
            'section:id,name,code',
            'semester:id,name,term',
        ]);

        return response()->json([
            'success' => true,
            'data' => $studentSection,
        ]);
    }

    public function update(Request $request, StudentSection $studentSection): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $studentSection->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Student section updated successfully.',
            'data' => $studentSection,
        ]);
    }

    public function destroy(StudentSection $studentSection): JsonResponse
    {
        $studentSection->delete();

        return response()->json([
            'success' => true,
            'message' => 'Student section removed successfully.',
        ]);
    }
}
