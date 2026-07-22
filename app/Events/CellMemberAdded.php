<?php

namespace App\Events;

use App\Events\Contracts\RoutingTriggerEvent;
use App\Models\Cell;
use App\Models\Person;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CellMemberAdded implements RoutingTriggerEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Cell $cell,
        public readonly Person $person,
    ) {
    }

    public function eventType(): string
    {
        return 'cell_member.added';
    }

    public function personId(): int
    {
        return $this->person->id;
    }

    public function toPayload(): array
    {
        return [
            'cell_id' => $this->cell->id,
            'cell_name' => $this->cell->name,
            'cell_leader' => $this->cell->leader?->full_name,
        ];
    }
}
