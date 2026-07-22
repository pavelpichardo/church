<?php

namespace App\Http\Resources\Doors;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoorReferralResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'door_id' => $this->door_id,
            'person_id' => $this->person_id,
            'source' => $this->source?->value,
            'source_label' => $this->source?->label(),
            'category' => $this->category,
            'priority' => $this->priority?->value,
            'priority_label' => $this->priority?->label(),
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'notes' => $this->notes,
            'due_date' => $this->due_date?->toDateString(),
            'completed_at' => $this->completed_at?->toDateTimeString(),
            'ai_confidence' => $this->ai_confidence,
            'ai_reasoning' => $this->ai_reasoning,
            'is_ai_generated' => $this->ai_inference_id !== null,
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
                'color' => $this->door->color,
            ]),
            'assigned_to' => $this->whenLoaded('assignedTo', fn () => $this->assignedTo ? [
                'id' => $this->assignedTo->id,
                'full_name' => $this->assignedTo->full_name,
            ] : null),
            'source_cell' => $this->whenLoaded('sourceCell', fn () => $this->sourceCell ? [
                'id' => $this->sourceCell->id,
                'name' => $this->sourceCell->name,
            ] : null),
            'source_user' => $this->whenLoaded('sourceUser', fn () => $this->sourceUser ? [
                'id' => $this->sourceUser->id,
                'name' => $this->sourceUser->name,
            ] : null),
            'triggering_rule' => $this->whenLoaded('triggeringRule', fn () => $this->triggeringRule ? [
                'id' => $this->triggeringRule->id,
                'name' => $this->triggeringRule->name,
            ] : null),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
