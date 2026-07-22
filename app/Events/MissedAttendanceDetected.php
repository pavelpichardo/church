<?php

namespace App\Events;

use App\Events\Contracts\RoutingTriggerEvent;
use App\Models\Person;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MissedAttendanceDetected implements RoutingTriggerEvent
{
    use Dispatchable, SerializesModels;

    /**
     * @param  array<int, int>  $missedEventIds
     */
    public function __construct(
        public readonly Person $person,
        public readonly int $missedCount,
        public readonly array $missedEventIds = [],
    ) {
    }

    public function eventType(): string
    {
        return 'attendance.missed_3';
    }

    public function personId(): int
    {
        return $this->person->id;
    }

    public function toPayload(): array
    {
        return [
            'missed_count' => $this->missedCount,
            'missed_event_ids' => $this->missedEventIds,
        ];
    }
}
