<?php

namespace App\Providers;

use App\Models\AttendanceRecord;
use App\Models\DiscipleshipAssignment;
use App\Models\Event;
use App\Models\MaterialLoan;
use App\Models\MembershipStageHistory;
use App\Models\Person;
use App\Models\PersonMembership;
use App\Support\Audit\AuditObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register audit observer on key models
        $auditedModels = [
            Person::class,
            PersonMembership::class,
            MembershipStageHistory::class,
            DiscipleshipAssignment::class,
            MaterialLoan::class,
            Event::class,
            AttendanceRecord::class,
        ];

        foreach ($auditedModels as $model) {
            $model::observe(AuditObserver::class);
        }
    }
}
