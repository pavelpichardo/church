<?php

namespace App\Events\Contracts;

/**
 * Marker contract for domain events that should fan out to the doors routing engine.
 * Implementers expose the slug consumed by Claude (e.g. "person.registered"),
 * the affected person, and any extra payload to include in the AI prompt.
 */
interface RoutingTriggerEvent
{
    public function eventType(): string;

    public function personId(): int;

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array;
}
