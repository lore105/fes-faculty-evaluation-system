<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\FacultyAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FacultyAssignmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $assignments = FacultyAssignment::with([
                'faculty:id,first_name,last_name,employee_id',
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
            'data' => $assignments,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'section_id' => 'required|exists:sections,id',
            'semester_id' => 'required|exists:semesters,id',
            'status' => 'in:active,inactive',
        ]);

        // Validate faculty role
        $faculty = User::findOrFail($validated['user_id']);
        if (!$faculty->hasRole('faculty')) {
            return response()->json([
                'success' => false,
                'message' => 'User is not a faculty member.',
            ], 422);
        }

        // Check duplicate assignment
        $existing = FacultyAssignment::where('user_id', $validated['user_id'])
            ->where('subject_id', $validated['subject_id'])
            ->where('section_id', $validated['section_id'])
            ->where('semester_id', $validated['semester_id'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Faculty is already assigned to this subject and section for this semester.',
            ], 422);
        }

        $assignment = FacultyAssignment::create($validated);
        $assignment->load([
            'faculty:id,first_name,last_name,employee_id',
            'subject:id,name,code',
            'section:id,name,code',
            'semester:id,name,term',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Faculty assigned successfully.',
            'data' => $assignment,
        ], 201);
    }

    public function show(FacultyAssignment $facultyAssignment): JsonResponse
    {
        $facultyAssignment->load([
            'faculty:id,first_name,last_name,employee_id',
            'subject:id,name,code',
            'section:id,name,code',
            'semester:id,name,term',
        ]);

        return response()->json([
            'success' => true,
            'data' => $facultyAssignment,
        ]);
    }

    public function update(Request $request, FacultyAssignment $facultyAssignment): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $facultyAssignment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Faculty assignment updated successfully.',
            'data' => $facultyAssignment,
        ]);
    }

    public function destroy(FacultyAssignment $facultyAssignment): JsonResponse
    {
        $facultyAssignment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Faculty assignment removed successfully.',
        ]);
    }
}
