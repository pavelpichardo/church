<?php

namespace App\Http\Resources\Library;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialLoanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'study_material_id' => $this->study_material_id,
            'person_id' => $this->person_id,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'assigned_at' => $this->assigned_at?->toDateTimeString(),
            'due_at' => $this->due_at?->toDateString(),
            'returned_at' => $this->returned_at?->toDateTimeString(),
            'notes' => $this->notes,
            'person' => $this->whenLoaded('person', fn () => [
                'id' => $this->person->id,
                'full_name' => $this->person->full_name,
            ]),
            'study_material' => $this->whenLoaded('studyMaterial', fn () => [
                'id' => $this->studyMaterial->id,
                'title' => $this->studyMaterial->title,
            ]),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
