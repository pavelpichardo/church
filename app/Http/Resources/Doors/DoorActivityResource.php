<?php

namespace App\Http\Resources\Doors;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoorActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'door_id' => $this->door_id,
            'title' => $this->title,
            'description' => $this->description,
            'scheduled_at' => $this->scheduled_at?->toDateTimeString(),
            'location' => $this->location,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'participants_count' => $this->whenCounted('participants'),
            'participants' => DoorActivityParticipantResource::collection($this->whenLoaded('participants')),
            'door' => $this->whenLoaded('door', fn () => [
                'id' => $this->door->id,
                'code' => $this->door->code?->value,
                'name' => $this->door->name,
            ]),
            'created_by' => $this->whenLoaded('createdBy', fn () => $this->createdBy ? [
                'id' => $this->createdBy->id,
                'name' => $this->createdBy->name,
            ] : null),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
