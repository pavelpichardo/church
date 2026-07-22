<?php

namespace App\Events;

use App\Events\Contracts\RoutingTriggerEvent;
use App\Models\Event;
use App\Models\Person;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CongressCreated implements RoutingTriggerEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Event $event,
        public readonly ?Person $coordinator = null,
    ) {
    }

    public function eventType(): string
    {
        return 'congress.created';
    }

    public function personId(): int
    {
        // Congress events route via the coordinator if known; otherwise use a placeholder
        // (the listener will skip if person_id doesn't resolve).
        return $this->coordinator?->id ?? 0;
    }

    public function toPayload(): array
    {
        return [
            'event_id' => $this->event->id,
            'event_name' => $this->event->title,
            'starts_at' => $this->event->starts_at?->toDateTimeString(),
            'coordinator' => $this->coordinator?->full_name,
        ];
    }
}
