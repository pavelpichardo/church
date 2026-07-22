<?php

namespace App\Http\Resources\Doors;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoorActivityParticipantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'door_activity_id' => $this->door_activity_id,
            'attended' => $this->attended,
            'notes' => $this->notes,
            'person' => $this->whenLoaded('person', fn () => [
                'id' => $this->person->id,
                'full_name' => $this->person->full_name,
            ]),
        ];
    }
}
