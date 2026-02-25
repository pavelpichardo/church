<?php

namespace App\Http\Resources\Discipleship;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscipleshipAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'discipleship_id' => $this->discipleship_id,
            'person_id' => $this->person_id,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'notes' => $this->notes,
            'person' => $this->whenLoaded('person', fn () => [
                'id' => $this->person->id,
                'full_name' => $this->person->full_name,
            ]),
            'discipleship' => $this->whenLoaded('discipleship', fn () => [
                'id' => $this->discipleship->id,
                'name' => $this->discipleship->name,
            ]),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
