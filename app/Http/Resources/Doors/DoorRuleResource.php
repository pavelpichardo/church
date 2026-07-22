<?php

namespace App\Http\Resources\Doors;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoorRuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'door_id' => $this->door_id,
            'name' => $this->name,
            'description' => $this->description,
            'event_types' => $this->event_types,
            'priority_hint' => $this->priority_hint?->value,
            'priority_hint_label' => $this->priority_hint?->label(),
            'is_enabled' => $this->is_enabled,
            'door' => $this->whenLoaded('door', fn () => [
                'id' => $this->door->id,
                'code' => $this->door->code?->value,
                'name' => $this->door->name,
            ]),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
