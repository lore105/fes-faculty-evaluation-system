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
            Route::apiResource('users', \App\Http\Controllers\Api\V1\Admin\UserController::class);
            Route::apiResource('roles', \App\Http\Controllers\Api\V1\Admin\RoleController::class);
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
