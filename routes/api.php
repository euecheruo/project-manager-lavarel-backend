<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\Admin\UserController;

/*
|--------------------------------------------------------------------------
| Public Routes (No Token Required)
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);

Route::post('/register', [AuthController::class, 'register']);

Route::get('/health', fn() => response()->json(['status' => 'API is running']));

/*
|--------------------------------------------------------------------------
| Protected Routes (Requires Valid JWT)
|--------------------------------------------------------------------------
*/

Route::middleware(['jwt.auth'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh-token', [AuthController::class, 'refresh']);
    Route::get('/profile', fn(\Illuminate\Http\Request $request) => new \App\Http\Resources\UserResource($request->user()));

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/my-reviews', [ReviewController::class, 'myReviews']);

    Route::apiResource('projects', ProjectController::class);

    Route::get('/projects/{project}/reviews', [ReviewController::class, 'index']);
    Route::post('/projects/{project}/reviews', [ReviewController::class, 'store']);

    Route::put('/reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);

    Route::apiResource('teams', TeamController::class);

    /*
    |--------------------------------------------------------------------------
    | Executive Only Routes (Strict Role Check)
    |--------------------------------------------------------------------------
    | These endpoints are critical administrative functions.
    | We apply the 'role:Executive' middleware (mapped in bootstrap/app.php).
    */
    Route::middleware(['role:Executive'])->group(function () {

        Route::apiResource('users', UserController::class);

        Route::post('/teams/{team}/members', [TeamController::class, 'addMember']);
        Route::delete('/teams/{team}/members/{userId}', [TeamController::class, 'removeMember']);

        Route::prefix('assignments')->group(function () {

            Route::post('/project-teams', [AssignmentController::class, 'assignTeams']);

            Route::post('/advisors', [AssignmentController::class, 'assignAdvisor']);

            Route::delete('/advisors/{project}/{userId}', [AssignmentController::class, 'removeAdvisor']);
        });
    });
});
