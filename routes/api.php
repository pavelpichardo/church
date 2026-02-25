<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DiscipleshipController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\MembershipController;
use App\Http\Controllers\Api\PersonController;
use App\Http\Controllers\Api\StudyMaterialController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Auth (public)
    Route::post('auth/login', [AuthController::class, 'login']);

    // Protected
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        // People
        Route::apiResource('people', PersonController::class);

        // Membership
        Route::get('membership', [MembershipController::class, 'index']);
        Route::get('membership/stages', [MembershipController::class, 'stages']);
        Route::get('people/{person}/membership', [MembershipController::class, 'show']);
        Route::post('people/{person}/membership/advance', [MembershipController::class, 'advance']);
        Route::post('people/{person}/membership/approve', [MembershipController::class, 'approve']);

        // Discipleship
        Route::apiResource('discipleships', DiscipleshipController::class);
        Route::get('discipleships/{discipleship}/assignments', [DiscipleshipController::class, 'assignments']);
        Route::post('discipleships/{discipleship}/assignments', [DiscipleshipController::class, 'assign']);
        Route::patch('discipleships/{discipleship}/assignments/{assignment}/complete', [DiscipleshipController::class, 'completeAssignment']);

        // Library
        Route::apiResource('library/materials', StudyMaterialController::class)
            ->parameter('materials', 'studyMaterial');
        Route::get('library/materials/{studyMaterial}/loans', [StudyMaterialController::class, 'loans']);
        Route::post('library/materials/{studyMaterial}/loans', [StudyMaterialController::class, 'loan']);
        Route::patch('library/materials/{studyMaterial}/loans/{loan}/return', [StudyMaterialController::class, 'returnLoan']);

        // Events & Attendance
        Route::apiResource('events', EventController::class);
        Route::get('events/{event}/attendance', [EventController::class, 'attendance']);
        Route::post('events/{event}/attendance', [EventController::class, 'recordAttendance']);
        Route::post('events/{event}/attendance/bulk', [EventController::class, 'bulkAttendance']);
    });
});
