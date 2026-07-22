<?php

namespace App\Providers;

use Anthropic\Client as AnthropicSdkClient;
use App\Models\AttendanceRecord;
use App\Models\Cell;
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
        $this->app->singleton(AnthropicSdkClient::class, function ($app) {
            return new AnthropicSdkClient(
                apiKey: (string) config('services.anthropic.api_key'),
            );
        });
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
            Cell::class,
        ];

        foreach ($auditedModels as $model) {
            $model::observe(AuditObserver::class);
        }

        // Routing-trigger domain events fan out to the doors AI pipeline via
        // Laravel's event auto-discovery (App\Listeners\QueueDoorRoutingEvaluation
        // type-hints the RoutingTriggerEvent interface on its handle() method).
    }
}
