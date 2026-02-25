<?php

namespace App\Http\Resources\Attendance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_id' => $this->event_id,
            'person_id' => $this->person_id,
            'checked_in_at' => $this->checked_in_at?->toDateTimeString(),
            'checkin_method' => $this->checkin_method?->value,
            'notes' => $this->notes,
            'person' => $this->whenLoaded('person', fn () => [
                'id' => $this->person->id,
                'full_name' => $this->person->full_name,
            ]),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
