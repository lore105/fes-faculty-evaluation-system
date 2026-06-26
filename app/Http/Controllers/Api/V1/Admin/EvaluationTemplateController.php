<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\EvaluationTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EvaluationTemplateController extends Controller
{
    public function index(): JsonResponse
    {
        $templates = EvaluationTemplate::withCount(['categories', 'evaluationPeriods'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $templates,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:draft,published,archived',
            'is_active' => 'boolean',
        ]);

        $template = EvaluationTemplate::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Evaluation template created successfully.',
            'data' => $template,
        ], 201);
    }

    public function show(EvaluationTemplate $evaluationTemplate): JsonResponse
    {
        $evaluationTemplate->load([
            'categories.questions',
            'ratingScales',
            'interpretationRules.recommendationRules',
        ]);

        return response()->json([
            'success' => true,
            'data' => $evaluationTemplate,
        ]);
    }

    public function update(Request $request, EvaluationTemplate $evaluationTemplate): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:draft,published,archived',
            'is_active' => 'boolean',
        ]);

        $evaluationTemplate->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Evaluation template updated successfully.',
            'data' => $evaluationTemplate,
        ]);
    }

    public function destroy(EvaluationTemplate $evaluationTemplate): JsonResponse
    {
        $evaluationTemplate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Evaluation template deleted successfully.',
        ]);
    }
}
