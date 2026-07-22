<?php

namespace App\Events;

use App\Events\Contracts\RoutingTriggerEvent;
use App\Models\Person;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PersonHealthStatusReported implements RoutingTriggerEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Person $person,
        public readonly string $situation,
        public readonly ?string $severity = null,
    ) {
    }

    public function eventType(): string
    {
        return 'person.health_status_reported';
    }

    public function personId(): int
    {
        return $this->person->id;
    }

    public function toPayload(): array
    {
        return [
            'situation' => $this->situation,
            'severity' => $this->severity,
        ];
    }
}
