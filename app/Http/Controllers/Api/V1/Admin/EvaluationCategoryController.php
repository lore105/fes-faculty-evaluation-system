<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\EvaluationCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EvaluationCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categories = EvaluationCategory::with('template')
            ->when($request->evaluation_template_id, fn($q) => $q->where('evaluation_template_id', $request->evaluation_template_id))
            ->withCount('questions')
            ->orderBy('order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'evaluation_template_id' => 'required|exists:evaluation_templates,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weight' => 'required|numeric|min:0|max:100',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $category = EvaluationCategory::create($validated);
        $category->load('template');

        return response()->json([
            'success' => true,
            'message' => 'Evaluation category created successfully.',
            'data' => $category,
        ], 201);
    }

    public function show(EvaluationCategory $evaluationCategory): JsonResponse
    {
        $evaluationCategory->load(['template', 'questions']);

        return response()->json([
            'success' => true,
            'data' => $evaluationCategory,
        ]);
    }

    public function update(Request $request, EvaluationCategory $evaluationCategory): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'weight' => 'sometimes|numeric|min:0|max:100',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $evaluationCategory->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Evaluation category updated successfully.',
            'data' => $evaluationCategory,
        ]);
    }

    public function destroy(EvaluationCategory $evaluationCategory): JsonResponse
    {
        $evaluationCategory->delete();

        return response()->json([
            'success' => true,
            'message' => 'Evaluation category deleted successfully.',
        ]);
    }
}
