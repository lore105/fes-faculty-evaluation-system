<?php

namespace App\Http\Controllers\Api\V1\Evaluation;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\EvaluationPeriod;
use App\Models\EvaluationResponse;
use App\Models\EvaluationComment;
use App\Models\StudentEnrollment;
use App\Models\FacultyAssignment;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class EvaluationController extends Controller
{
    // Get eligible faculty for evaluation
    public function getEligibleFaculty(Request $request): JsonResponse
    {
        $request->validate([
            'evaluation_period_id' => 'required|exists:evaluation_periods,id',
        ]);

        $user = $request->user();
        $period = EvaluationPeriod::findOrFail($request->evaluation_period_id);

        // Validate period is open
        if (!$period->isOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'Evaluation period is not open.',
            ], 422);
        }

        // Get faculty based on student enrollments
        if ($user->hasRole('student')) {
            $enrollments = StudentEnrollment::where('user_id', $user->id)
                ->where('semester_id', $period->semester_id)
                ->where('status', 'enrolled')
                ->with(['subject:id,name,code', 'section:id,name,code'])
                ->get();

            $eligibleFaculty = [];
            foreach ($enrollments as $enrollment) {
                $assignments = FacultyAssignment::where('subject_id', $enrollment->subject_id)
                    ->where('section_id', $enrollment->section_id)
                    ->where('semester_id', $period->semester_id)
                    ->where('status', 'active')
                    ->with('faculty:id,first_name,last_name,employee_id')
                    ->get();

                foreach ($assignments as $assignment) {
                    // Check if already evaluated
                    $alreadyEvaluated = Evaluation::where('evaluation_period_id', $period->id)
                        ->where('evaluator_id', $user->id)
                        ->where('evaluatee_id', $assignment->user_id)
                        ->where('evaluation_type', 'student')
                        ->exists();

                    $eligibleFaculty[] = [
                        'faculty_id' => $assignment->user_id,
                        'faculty_name' => $assignment->faculty->full_name,
                        'subject' => $enrollment->subject,
                        'section' => $enrollment->section,
                        'already_evaluated' => $alreadyEvaluated,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $eligibleFaculty,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Role not supported for this endpoint.',
        ], 422);
    }

    // Submit evaluation
    public function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'evaluation_period_id' => 'required|exists:evaluation_periods,id',
            'evaluatee_id' => 'required|exists:users,id',
            'evaluation_type' => 'required|in:student,peer,supervisor',
            'subject_id' => 'nullable|exists:subjects,id',
            'section_id' => 'nullable|exists:sections,id',
            'responses' => 'required|array|min:1',
            'responses.*.question_id' => 'required|exists:evaluation_questions,id',
            'responses.*.category_id' => 'required|exists:evaluation_categories,id',
            'responses.*.rating_value' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        $period = EvaluationPeriod::findOrFail($validated['evaluation_period_id']);

        // Validate period is open
        if (!$period->isOpen()) {
            return response()->json([
                'success' => false,
                'message' => 'Evaluation period is not open.',
            ], 422);
        }

        // Validate evaluation type is allowed
        if ($validated['evaluation_type'] === 'student' && !$period->allow_student_evaluation) {
            return response()->json([
                'success' => false,
                'message' => 'Student evaluation is not allowed for this period.',
            ], 422);
        }

        // Check duplicate submission
        $existing = Evaluation::where('evaluation_period_id', $period->id)
            ->where('evaluator_id', $user->id)
            ->where('evaluatee_id', $validated['evaluatee_id'])
            ->where('evaluation_type', $validated['evaluation_type'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You have already submitted an evaluation for this faculty member.',
            ], 422);
        }

        // Validate student eligibility
        if ($validated['evaluation_type'] === 'student') {
            $isEnrolled = StudentEnrollment::where('user_id', $user->id)
                ->where('subject_id', $validated['subject_id'])
                ->where('section_id', $validated['section_id'])
                ->where('semester_id', $period->semester_id)
                ->where('status', 'enrolled')
                ->exists();

            if (!$isEnrolled) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not enrolled in this subject.',
                ], 422);
            }

            $isFacultyAssigned = FacultyAssignment::where('user_id', $validated['evaluatee_id'])
                ->where('subject_id', $validated['subject_id'])
                ->where('section_id', $validated['section_id'])
                ->where('semester_id', $period->semester_id)
                ->where('status', 'active')
                ->exists();

            if (!$isFacultyAssigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'Faculty is not assigned to this subject and section.',
                ], 422);
            }
        }

        // Process submission in transaction
        DB::beginTransaction();

        try {
            // Compute scores
            $categoryScores = [];
            $responses = $validated['responses'];

            foreach ($responses as $response) {
                $categoryId = $response['category_id'];
                if (!isset($categoryScores[$categoryId])) {
                    $categoryScores[$categoryId] = ['total' => 0, 'count' => 0];
                }
                $categoryScores[$categoryId]['total'] += $response['rating_value'];
                $categoryScores[$categoryId]['count']++;
            }

            // Get category weights
            $template = $period->load('template.categories', 'template.interpretationRules')->template;
            $categories = $template->categories->keyBy('id');
            $weightedScore = 0;
            $totalWeight = 0;

            foreach ($categoryScores as $categoryId => $scores) {
                $category = $categories[$categoryId] ?? null;
                if ($category) {
                    $avgScore = $scores['total'] / $scores['count'];
                    $weight = $category->weight / 100;
                    $weightedScore += $avgScore * $weight;
                    $totalWeight += $weight;
                }
            }

            $totalScore = $totalWeight > 0 ? $weightedScore / $totalWeight : 0;

            // Get performance rating
            $interpretationRule = $template->interpretationRules
                ->first(fn($rule) => $totalScore >= $rule->min_score && $totalScore <= $rule->max_score);

            $performanceRating = $interpretationRule?->label ?? 'Unrated';

            // Create evaluation
            $evaluation = Evaluation::create([
                'evaluation_period_id' => $period->id,
                'evaluator_id' => $user->id,
                'evaluatee_id' => $validated['evaluatee_id'],
                'evaluation_type' => $validated['evaluation_type'],
                'subject_id' => $validated['subject_id'] ?? null,
                'section_id' => $validated['section_id'] ?? null,
                'status' => 'submitted',
                'total_score' => round($totalScore, 2),
                'performance_rating' => $performanceRating,
                'submitted_at' => now(),
            ]);

            // Store responses
            foreach ($responses as $response) {
                EvaluationResponse::create([
                    'evaluation_id' => $evaluation->id,
                    'evaluation_question_id' => $response['question_id'],
                    'evaluation_category_id' => $response['category_id'],
                    'rating_value' => $response['rating_value'],
                    'text_response' => $response['text_response'] ?? null,
                ]);
            }

            // Store comment
            if (!empty($validated['comment'])) {
                EvaluationComment::create([
                    'evaluation_id' => $evaluation->id,
                    'comment' => $validated['comment'],
                ]);
            }

            // Audit log
            AuditLogService::log(
                action: 'submit_evaluation',
                userId: $user->id,
                modelType: 'Evaluation',
                modelId: $evaluation->id,
                newValues: [
                    'evaluation_period_id' => $period->id,
                    'evaluatee_id' => $validated['evaluatee_id'],
                    'evaluation_type' => $validated['evaluation_type'],
                    'total_score' => $evaluation->total_score,
                    'performance_rating' => $evaluation->performance_rating,
                ],
                request: $request
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Evaluation submitted successfully.',
                'data' => [
                    'evaluation_id' => $evaluation->id,
                    'total_score' => $evaluation->total_score,
                    'performance_rating' => $evaluation->performance_rating,
                    'submitted_at' => $evaluation->submitted_at,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Evaluation submission failed. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Get evaluation results for faculty
    public function results(Request $request): JsonResponse
    {
        $request->validate([
            'evaluation_period_id' => 'required|exists:evaluation_periods,id',
        ]);

        $user = $request->user();
        $period = EvaluationPeriod::findOrFail($request->evaluation_period_id);

        // Results only visible after period is closed or published
        if (!$period->isClosed() && !$period->isPublished()) {
            return response()->json([
                'success' => false,
                'message' => 'Results are not yet available.',
            ], 422);
        }

        $evaluations = Evaluation::where('evaluatee_id', $user->id)
            ->where('evaluation_period_id', $period->id)
            ->where('status', 'submitted')
            ->with(['responses.category', 'comments'])
            ->get();

        $totalScore = $evaluations->avg('total_score');
        $performanceRating = $evaluations->first()?->performance_rating ?? 'Unrated';

        return response()->json([
            'success' => true,
            'data' => [
                'evaluation_period' => $period->name,
                'total_evaluations' => $evaluations->count(),
                'average_score' => round($totalScore, 2),
                'performance_rating' => $performanceRating,
                'evaluations' => $evaluations,
            ],
        ]);
    }
}
