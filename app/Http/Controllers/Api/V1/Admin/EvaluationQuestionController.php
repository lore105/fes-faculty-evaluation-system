<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\EvaluationQuestion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EvaluationQuestionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $questions = EvaluationQuestion::with('category.template')
            ->when($request->evaluation_category_id, fn($q) => $q->where('evaluation_category_id', $request->evaluation_category_id))
            ->orderBy('order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $questions,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'evaluation_category_id' => 'required|exists:evaluation_categories,id',
            'question' => 'required|string',
            'type' => 'in:rating,text,multiple_choice',
            'order' => 'integer|min:0',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $question = EvaluationQuestion::create($validated);
        $question->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Evaluation question created successfully.',
            'data' => $question,
        ], 201);
    }

    public function show(EvaluationQuestion $evaluationQuestion): JsonResponse
    {
        $evaluationQuestion->load('category.template');

        return response()->json([
            'success' => true,
            'data' => $evaluationQuestion,
        ]);
    }

    public function update(Request $request, EvaluationQuestion $evaluationQuestion): JsonResponse
    {
        $validated = $request->validate([
            'question' => 'sometimes|string',
            'type' => 'in:rating,text,multiple_choice',
            'order' => 'integer|min:0',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $evaluationQuestion->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Evaluation question updated successfully.',
            'data' => $evaluationQuestion,
        ]);
    }

    public function destroy(EvaluationQuestion $evaluationQuestion): JsonResponse
    {
        $evaluationQuestion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Evaluation question deleted successfully.',
        ]);
    }
}
