<?php

namespace App\Events;

use App\Events\Contracts\RoutingTriggerEvent;
use App\Models\Person;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PersonRegistered implements RoutingTriggerEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Person $person)
    {
    }

    public function eventType(): string
    {
        return 'person.registered';
    }

    public function personId(): int
    {
        return $this->person->id;
    }

    public function toPayload(): array
    {
        return [
            'status' => $this->person->status?->value,
            'how_found_us' => $this->person->how_found_us?->value,
            'first_visit_date' => $this->person->first_visit_date?->toDateString(),
        ];
    }
}
