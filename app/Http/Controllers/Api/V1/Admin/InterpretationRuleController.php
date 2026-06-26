<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\InterpretationRule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InterpretationRuleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $rules = InterpretationRule::with('template')
            ->when($request->evaluation_template_id, fn($q) => $q->where('evaluation_template_id', $request->evaluation_template_id))
            ->withCount('recommendationRules')
            ->orderBy('min_score')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rules,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'evaluation_template_id' => 'required|exists:evaluation_templates,id',
            'label' => 'required|string|max:255',
            'min_score' => 'required|numeric|min:0',
            'max_score' => 'required|numeric|gt:min_score',
            'description' => 'nullable|string',
            'color_code' => 'nullable|string|max:7',
            'order' => 'integer|min:0',
        ]);

        $rule = InterpretationRule::create($validated);
        $rule->load('template');

        return response()->json([
            'success' => true,
            'message' => 'Interpretation rule created successfully.',
            'data' => $rule,
        ], 201);
    }

    public function show(InterpretationRule $interpretationRule): JsonResponse
    {
        $interpretationRule->load(['template', 'recommendationRules']);

        return response()->json([
            'success' => true,
            'data' => $interpretationRule,
        ]);
    }

    public function update(Request $request, InterpretationRule $interpretationRule): JsonResponse
    {
        $validated = $request->validate([
            'label' => 'sometimes|string|max:255',
            'min_score' => 'sometimes|numeric|min:0',
            'max_score' => 'sometimes|numeric',
            'description' => 'nullable|string',
            'color_code' => 'nullable|string|max:7',
            'order' => 'integer|min:0',
        ]);

        $interpretationRule->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Interpretation rule updated successfully.',
            'data' => $interpretationRule,
        ]);
    }

    public function destroy(InterpretationRule $interpretationRule): JsonResponse
    {
        $interpretationRule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Interpretation rule deleted successfully.',
        ]);
    }
}
