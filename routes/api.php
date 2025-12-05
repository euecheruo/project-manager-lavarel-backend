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

// This 'register' is actually "Account Setup" for invited employees
Route::post('/register', [AuthController::class, 'register']);

// Optional: Public health check
Route::get('/health', fn() => response()->json(['status' => 'API is running']));

/*
|--------------------------------------------------------------------------
| Protected Routes (Requires Valid JWT)
|--------------------------------------------------------------------------
*/

Route::middleware(['jwt.auth'])->group(function () {

    // --- Authentication Management ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh-token', [AuthController::class, 'refresh']);
    Route::get('/profile', fn(\Illuminate\Http\Request $request) => new \App\Http\Resources\UserResource($request->user()));

    // --- Dashboard ---
    // Controller logic branches based on Role (Exec/Mgr/Assoc)
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // --- My Reviews (Personal Audit Log) ---
    Route::get('/my-reviews', [ReviewController::class, 'myReviews']);

    // --- Projects ---
    // Viewing is open to all (Controller filters based on access)
    // Creating/Updating/Deleting is restricted via Policy checks in the Controller
    Route::apiResource('projects', ProjectController::class);

    // --- Reviews (Nested under Projects) ---
    // GET /api/projects/1/reviews
    // POST /api/projects/1/reviews
    Route::get('/projects/{project}/reviews', [ReviewController::class, 'index']);
    Route::post('/projects/{project}/reviews', [ReviewController::class, 'store']);

    // Review Management (Edit/Delete specific review)
    Route::put('/reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);

    // --- Teams ---
    Route::apiResource('teams', TeamController::class);

    /*
    |--------------------------------------------------------------------------
    | Executive Only Routes (Strict Role Check)
    |--------------------------------------------------------------------------
    | These endpoints are critical administrative functions.
    | We apply the 'role:Executive' middleware (mapped in bootstrap/app.php).
    */
    Route::middleware(['role:Executive'])->group(function () {

        // 1. User Administration
        // Full CRUD for employees
        Route::apiResource('users', UserController::class);

        // 2. Complex Assignments
        Route::prefix('assignments')->group(function () {

            // Assign entire Teams to a Project
            Route::post('/project-teams', [AssignmentController::class, 'assignTeams']);

            // Assign specific Internal Advisors (Contextual Role)
            Route::post('/advisors', [AssignmentController::class, 'assignAdvisor']);

            // Remove Advisor
            Route::delete('/advisors/{project}/{userId}', [AssignmentController::class, 'removeAdvisor']);
        });
    });
});
