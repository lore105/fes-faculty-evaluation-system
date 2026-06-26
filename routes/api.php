<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| FES API Routes - Version 1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Auth Routes
    Route::prefix('auth')->group(function () {
        Route::post('login', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'login']);
        Route::post('logout', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'logout'])->middleware('auth:sanctum');
        Route::get('me', [\App\Http\Controllers\Api\V1\Auth\AuthController::class, 'me'])->middleware('auth:sanctum');
    });

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {

        // Admin Routes
        Route::prefix('admin')->middleware('role:super_admin|administrator')->group(function () {

            // User & Role Management
            Route::apiResource('users', \App\Http\Controllers\Api\V1\Admin\UserController::class);
            Route::apiResource('roles', \App\Http\Controllers\Api\V1\Admin\RoleController::class);

            // Academic Structure
            Route::apiResource('colleges', \App\Http\Controllers\Api\V1\Admin\CollegeController::class);
            Route::apiResource('departments', \App\Http\Controllers\Api\V1\Admin\DepartmentController::class);
            Route::apiResource('programs', \App\Http\Controllers\Api\V1\Admin\ProgramController::class);
            Route::apiResource('subjects', \App\Http\Controllers\Api\V1\Admin\SubjectController::class);
            Route::apiResource('sections', \App\Http\Controllers\Api\V1\Admin\SectionController::class);

            // Academic Calendar
            Route::apiResource('academic-years', \App\Http\Controllers\Api\V1\Admin\AcademicYearController::class);
            Route::apiResource('semesters', \App\Http\Controllers\Api\V1\Admin\SemesterController::class);

            // Import Routes
            Route::prefix('import')->group(function () {
                Route::post('students', [\App\Http\Controllers\Api\V1\Admin\ImportController::class, 'importStudents']);
                Route::post('faculty', [\App\Http\Controllers\Api\V1\Admin\ImportController::class, 'importFaculty']);
            });

            // Academic Assignments
            Route::apiResource('student-enrollments', \App\Http\Controllers\Api\V1\Admin\StudentEnrollmentController::class);
            Route::apiResource('faculty-assignments', \App\Http\Controllers\Api\V1\Admin\FacultyAssignmentController::class);
            Route::apiResource('student-sections', \App\Http\Controllers\Api\V1\Admin\StudentSectionController::class);

            // Evaluation Governance
            Route::apiResource('evaluation-templates', \App\Http\Controllers\Api\V1\Admin\EvaluationTemplateController::class);
            Route::apiResource('evaluation-categories', \App\Http\Controllers\Api\V1\Admin\EvaluationCategoryController::class);
            Route::apiResource('evaluation-questions', \App\Http\Controllers\Api\V1\Admin\EvaluationQuestionController::class);
            Route::apiResource('rating-scales', \App\Http\Controllers\Api\V1\Admin\RatingScaleController::class);
            Route::apiResource('interpretation-rules', \App\Http\Controllers\Api\V1\Admin\InterpretationRuleController::class);
            Route::apiResource('recommendation-rules', \App\Http\Controllers\Api\V1\Admin\RecommendationRuleController::class);

            // Evaluation Period
            Route::apiResource('evaluation-periods', \App\Http\Controllers\Api\V1\Admin\EvaluationPeriodController::class);
        });

        // Faculty Routes
        Route::prefix('faculty')->middleware('role:faculty')->group(function () {
            Route::get('evaluations', [\App\Http\Controllers\Api\V1\Faculty\FacultyEvaluationController::class, 'index']);
            Route::get('results', [\App\Http\Controllers\Api\V1\Faculty\FacultyEvaluationController::class, 'results']);
        });

        // Student Routes
        Route::prefix('student')->middleware('role:student')->group(function () {
            Route::get('evaluations', [\App\Http\Controllers\Api\V1\Student\StudentEvaluationController::class, 'index']);
            Route::post('evaluations/submit', [\App\Http\Controllers\Api\V1\Student\StudentEvaluationController::class, 'submit']);
        });

    });

});
