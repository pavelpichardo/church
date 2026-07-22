<?php

namespace App\Events;

use App\Events\Contracts\RoutingTriggerEvent;
use App\Models\Person;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MembershipStageAdvanced implements RoutingTriggerEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Person $person,
        public readonly string $toStageName,
        public readonly ?string $fromStageName = null,
    ) {
    }

    public function eventType(): string
    {
        return 'membership_stage.advanced';
    }

    public function personId(): int
    {
        return $this->person->id;
    }

    public function toPayload(): array
    {
        return [
            'from_stage' => $this->fromStageName,
            'to_stage' => $this->toStageName,
        ];
    }
}
