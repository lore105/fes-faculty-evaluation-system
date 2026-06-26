<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StudentEnrollmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $enrollments = StudentEnrollment::with([
                'student:id,first_name,last_name,student_id',
                'subject:id,name,code',
                'section:id,name,code',
                'semester:id,name,term',
            ])
            ->when($request->semester_id, fn($q) => $q->where('semester_id', $request->semester_id))
            ->when($request->section_id, fn($q) => $q->where('section_id', $request->section_id))
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $enrollments,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'section_id' => 'required|exists:sections,id',
            'semester_id' => 'required|exists:semesters,id',
            'status' => 'in:enrolled,dropped,completed',
        ]);

        // Validate student role
        $student = User::findOrFail($validated['user_id']);
        if (!$student->hasRole('student')) {
            return response()->json([
                'success' => false,
                'message' => 'User is not a student.',
            ], 422);
        }

        // Check duplicate enrollment
        $existing = StudentEnrollment::where('user_id', $validated['user_id'])
            ->where('subject_id', $validated['subject_id'])
            ->where('semester_id', $validated['semester_id'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Student is already enrolled in this subject for this semester.',
            ], 422);
        }

        $enrollment = StudentEnrollment::create($validated);
        $enrollment->load([
            'student:id,first_name,last_name,student_id',
            'subject:id,name,code',
            'section:id,name,code',
            'semester:id,name,term',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Student enrolled successfully.',
            'data' => $enrollment,
        ], 201);
    }

    public function show(StudentEnrollment $studentEnrollment): JsonResponse
    {
        $studentEnrollment->load([
            'student:id,first_name,last_name,student_id',
            'subject:id,name,code',
            'section:id,name,code',
            'semester:id,name,term',
        ]);

        return response()->json([
            'success' => true,
            'data' => $studentEnrollment,
        ]);
    }

    public function update(Request $request, StudentEnrollment $studentEnrollment): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:enrolled,dropped,completed',
        ]);

        $studentEnrollment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Enrollment updated successfully.',
            'data' => $studentEnrollment,
        ]);
    }

    public function destroy(StudentEnrollment $studentEnrollment): JsonResponse
    {
        $studentEnrollment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Enrollment removed successfully.',
        ]);
    }
}
