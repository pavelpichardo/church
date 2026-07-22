<?php

namespace App\Http\Resources\Doors;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoorMemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'door_id' => $this->door_id,
            'role' => $this->role?->value,
            'role_label' => $this->role?->label(),
            'joined_at' => $this->joined_at?->toDateString(),
            'left_at' => $this->left_at?->toDateString(),
            'is_active' => $this->left_at === null,
            'notes' => $this->notes,
            'person' => $this->whenLoaded('person', fn () => [
                'id' => $this->person->id,
                'full_name' => $this->person->full_name,
                'phone' => $this->person->phone,
                'email' => $this->person->email,
            ]),
            'door' => $this->whenLoaded('door', fn () => [
                'id' => $this->door->id,
                'code' => $this->door->code?->value,
                'name' => $this->door->name,
            ]),
        ];
    }
}
