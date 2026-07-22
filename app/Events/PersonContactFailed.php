<?php

namespace App\Events;

use App\Events\Contracts\RoutingTriggerEvent;
use App\Models\Person;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PersonContactFailed implements RoutingTriggerEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Person $person,
        public readonly ?string $note = null,
    ) {
    }

    public function eventType(): string
    {
        return 'person.contact_failed';
    }

    public function personId(): int
    {
        return $this->person->id;
    }

    public function toPayload(): array
    {
        return [
            'note' => $this->note,
        ];
    }
}
