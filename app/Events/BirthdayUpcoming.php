<?php

namespace App\Events;

use App\Events\Contracts\RoutingTriggerEvent;
use App\Models\Person;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BirthdayUpcoming implements RoutingTriggerEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Person $person,
        public readonly int $daysUntil,
    ) {
    }

    public function eventType(): string
    {
        return 'birthday.upcoming_7d';
    }

    public function personId(): int
    {
        return $this->person->id;
    }

    public function toPayload(): array
    {
        return [
            'days_until' => $this->daysUntil,
            'birth_date' => $this->person->birth_date?->toDateString(),
        ];
    }
}
