<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecommendationRule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RecommendationRuleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $rules = RecommendationRule::with('interpretationRule')
            ->when($request->interpretation_rule_id, fn($q) => $q->where('interpretation_rule_id', $request->interpretation_rule_id))
            ->orderBy('order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rules,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'interpretation_rule_id' => 'required|exists:interpretation_rules,id',
            'recommendation' => 'required|string',
            'order' => 'integer|min:0',
        ]);

        $rule = RecommendationRule::create($validated);
        $rule->load('interpretationRule');

        return response()->json([
            'success' => true,
            'message' => 'Recommendation rule created successfully.',
            'data' => $rule,
        ], 201);
    }

    public function show(RecommendationRule $recommendationRule): JsonResponse
    {
        $recommendationRule->load('interpretationRule');

        return response()->json([
            'success' => true,
            'data' => $recommendationRule,
        ]);
    }

    public function update(Request $request, RecommendationRule $recommendationRule): JsonResponse
    {
        $validated = $request->validate([
            'recommendation' => 'sometimes|string',
            'order' => 'integer|min:0',
        ]);

        $recommendationRule->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Recommendation rule updated successfully.',
            'data' => $recommendationRule,
        ]);
    }

    public function destroy(RecommendationRule $recommendationRule): JsonResponse
    {
        $recommendationRule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Recommendation rule deleted successfully.',
        ]);
    }
}
