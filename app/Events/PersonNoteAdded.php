<?php

namespace App\Events;

use App\Events\Contracts\RoutingTriggerEvent;
use App\Models\Person;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PersonNoteAdded implements RoutingTriggerEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Person $person,
        public readonly string $noteBody,
        public readonly string $noteType = 'note',
    ) {
    }

    public function eventType(): string
    {
        return 'person.note_added';
    }

    public function personId(): int
    {
        return $this->person->id;
    }

    public function toPayload(): array
    {
        return [
            'note' => strip_tags($this->noteBody),
            'note_type' => $this->noteType,
        ];
    }
}
