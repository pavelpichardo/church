<?php

namespace App\Jobs;

use App\Events\MissedAttendanceDetected;
use App\Models\DoorReferral;
use App\Models\Event;
use App\Models\Person;
use App\Support\Enums\DoorCode;
use App\Support\Enums\EventType;
use App\Support\Enums\PersonStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DetectMissedAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $lastServices = Event::query()
            ->where('event_type', EventType::Service->value)
            ->where('starts_at', '<', now())
            ->where('starts_at', '>=', now()->subWeeks(6))
            ->orderByDesc('starts_at')
            ->limit(3)
            ->get();

        if ($lastServices->count() < 3) {
            Log::info('DetectMissedAttendanceJob: fewer than 3 recent services found, skipping.');
            return;
        }

        $serviceIds = $lastServices->pluck('id')->all();
        $visitacionDoorId = \App\Models\Door::where('code', DoorCode::Visitacion->value)->value('id');

        $missedMembers = Person::query()
            ->whereIn('status', [PersonStatus::ActiveMember->value, PersonStatus::Member->value])
            ->whereDoesntHave('attendanceRecords', function ($q) use ($serviceIds) {
                $q->whereIn('event_id', $serviceIds);
            })
            ->get();

        $emitted = 0;
        foreach ($missedMembers as $person) {
            // Debounce: skip if Visitación already has an open referral for this person
            $alreadyOpen = DoorReferral::query()
                ->where('person_id', $person->id)
                ->where('door_id', $visitacionDoorId)
                ->whereIn('status', ['pending', 'in_progress', 'pending_review'])
                ->exists();

            if ($alreadyOpen) {
                continue;
            }

            event(new MissedAttendanceDetected(
                person: $person,
                missedCount: 3,
                missedEventIds: $serviceIds,
            ));
            $emitted++;
        }

        Log::info("DetectMissedAttendanceJob: emitted {$emitted} events for missed attendance.");
    }
}
