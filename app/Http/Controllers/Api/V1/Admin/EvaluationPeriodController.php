<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\EvaluationPeriod;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EvaluationPeriodController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $periods = EvaluationPeriod::with([
                'academicYear',
                'semester',
                'template',
            ])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->semester_id, fn($q) => $q->where('semester_id', $request->semester_id))
            ->orderByDesc('start_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $periods,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
            'evaluation_template_id' => 'required|exists:evaluation_templates,id',
            'name' => 'required|string|max:255',
            'status' => 'in:draft,open,closed,published,archived',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'allow_student_evaluation' => 'boolean',
            'allow_peer_evaluation' => 'boolean',
            'allow_supervisor_evaluation' => 'boolean',
        ]);

        $period = EvaluationPeriod::create($validated);
        $period->load(['academicYear', 'semester', 'template']);

        return response()->json([
            'success' => true,
            'message' => 'Evaluation period created successfully.',
            'data' => $period,
        ], 201);
    }

    public function show(EvaluationPeriod $evaluationPeriod): JsonResponse
    {
        $evaluationPeriod->load([
            'academicYear',
            'semester',
            'template.categories.questions',
            'template.ratingScales',
            'template.interpretationRules',
        ]);

        return response()->json([
            'success' => true,
            'data' => $evaluationPeriod,
        ]);
    }

    public function update(Request $request, EvaluationPeriod $evaluationPeriod): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:draft,open,closed,published,archived',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'allow_student_evaluation' => 'boolean',
            'allow_peer_evaluation' => 'boolean',
            'allow_supervisor_evaluation' => 'boolean',
        ]);

        $evaluationPeriod->update($validated);
        $evaluationPeriod->load(['academicYear', 'semester', 'template']);

        return response()->json([
            'success' => true,
            'message' => 'Evaluation period updated successfully.',
            'data' => $evaluationPeriod,
        ]);
    }

    public function destroy(EvaluationPeriod $evaluationPeriod): JsonResponse
    {
        $evaluationPeriod->delete();

        return response()->json([
            'success' => true,
            'message' => 'Evaluation period deleted successfully.',
        ]);
    }
}
