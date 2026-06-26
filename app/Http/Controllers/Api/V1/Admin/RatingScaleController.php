<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\RatingScale;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RatingScaleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $scales = RatingScale::with('template')
            ->when($request->evaluation_template_id, fn($q) => $q->where('evaluation_template_id', $request->evaluation_template_id))
            ->orderBy('scale_value')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $scales,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'evaluation_template_id' => 'required|exists:evaluation_templates,id',
            'scale_value' => 'required|integer|min:1',
            'label' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $scale = RatingScale::create($validated);
        $scale->load('template');

        return response()->json([
            'success' => true,
            'message' => 'Rating scale created successfully.',
            'data' => $scale,
        ], 201);
    }

    public function show(RatingScale $ratingScale): JsonResponse
    {
        $ratingScale->load('template');

        return response()->json([
            'success' => true,
            'data' => $ratingScale,
        ]);
    }

    public function update(Request $request, RatingScale $ratingScale): JsonResponse
    {
        $validated = $request->validate([
            'scale_value' => 'sometimes|integer|min:1',
            'label' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        $ratingScale->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Rating scale updated successfully.',
            'data' => $ratingScale,
        ]);
    }

    public function destroy(RatingScale $ratingScale): JsonResponse
    {
        $ratingScale->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rating scale deleted successfully.',
        ]);
    }
}
