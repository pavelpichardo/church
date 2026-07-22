<?php

namespace App\Events;

use App\Events\Contracts\RoutingTriggerEvent;
use App\Models\Person;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PersonReturnedAfterAbsence implements RoutingTriggerEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Person $person,
        public readonly int $monthsAbsent,
    ) {
    }

    public function eventType(): string
    {
        return 'person.returned_after_absence';
    }

    public function personId(): int
    {
        return $this->person->id;
    }

    public function toPayload(): array
    {
        return [
            'months_absent' => $this->monthsAbsent,
        ];
    }
}
