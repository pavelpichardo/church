<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CellController;
use App\Http\Controllers\Api\DiscipleshipController;
use App\Http\Controllers\Api\DoorActivityController;
use App\Http\Controllers\Api\DoorAlertController;
use App\Http\Controllers\Api\DoorController;
use App\Http\Controllers\Api\DoorMemberController;
use App\Http\Controllers\Api\DoorReferralController;
use App\Http\Controllers\Api\DoorRuleController;
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

        // Cells
        Route::apiResource('cells', CellController::class);
        Route::get('cells/{cell}/members', [CellController::class, 'members']);
        Route::post('cells/{cell}/members', [CellController::class, 'addMember']);
        Route::delete('cells/{cell}/members/{person}', [CellController::class, 'removeMember']);
        Route::post('cells/{cell}/multiply', [CellController::class, 'multiply']);

        // Events & Attendance
        Route::apiResource('events', EventController::class);
        Route::get('events/{event}/attendance', [EventController::class, 'attendance']);
        Route::post('events/{event}/attendance', [EventController::class, 'recordAttendance']);
        Route::post('events/{event}/attendance/bulk', [EventController::class, 'bulkAttendance']);

        // Doors (read + update only — the 9 are fixed)
        Route::get('doors', [DoorController::class, 'index']);
        Route::get('doors/{door}', [DoorController::class, 'show']);
        Route::patch('doors/{door}', [DoorController::class, 'update']);

        // Door members (volunteers + leaders)
        Route::get('doors/{door}/members', [DoorMemberController::class, 'index']);
        Route::post('doors/{door}/members', [DoorMemberController::class, 'store']);
        Route::patch('doors/{door}/members/{member}', [DoorMemberController::class, 'update']);
        Route::delete('doors/{door}/members/{member}', [DoorMemberController::class, 'destroy']);

        // Door activities
        Route::get('doors/{door}/activities', [DoorActivityController::class, 'index']);
        Route::post('doors/{door}/activities', [DoorActivityController::class, 'store']);
        Route::get('doors/{door}/activities/{activity}', [DoorActivityController::class, 'show']);
        Route::patch('doors/{door}/activities/{activity}', [DoorActivityController::class, 'update']);
        Route::delete('doors/{door}/activities/{activity}', [DoorActivityController::class, 'destroy']);
        Route::post('doors/{door}/activities/{activity}/attendance', [DoorActivityController::class, 'recordAttendance']);

        // Door rules (natural-language rules — AI engine in PR 3 will consume them)
        Route::get('doors/{door}/rules', [DoorRuleController::class, 'index']);
        Route::post('doors/{door}/rules', [DoorRuleController::class, 'store']);
        Route::get('doors/{door}/rules/{rule}', [DoorRuleController::class, 'show']);
        Route::patch('doors/{door}/rules/{rule}', [DoorRuleController::class, 'update']);
        Route::delete('doors/{door}/rules/{rule}', [DoorRuleController::class, 'destroy']);
        Route::post('doors/{door}/rules/{rule}/toggle', [DoorRuleController::class, 'toggle']);

        // Door alerts
        Route::get('doors/{door}/alerts', [DoorAlertController::class, 'index']);
        Route::post('doors/{door}/alerts/{alert}/read', [DoorAlertController::class, 'markRead']);
        Route::post('doors/{door}/alerts/read-all', [DoorAlertController::class, 'markAllRead']);

        // Referrals (cross-door — the central "needs" entity)
        Route::get('referrals', [DoorReferralController::class, 'index']);
        Route::post('referrals', [DoorReferralController::class, 'store']);
        Route::get('referrals/{referral}', [DoorReferralController::class, 'show']);
        Route::patch('referrals/{referral}', [DoorReferralController::class, 'update']);
        Route::delete('referrals/{referral}', [DoorReferralController::class, 'destroy']);
        Route::post('referrals/{referral}/assign', [DoorReferralController::class, 'assign']);
        Route::post('referrals/{referral}/status', [DoorReferralController::class, 'changeStatus']);
    });
});
