<?php

namespace App\Http\Resources\Membership;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonMembershipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'person_id' => $this->person_id,
            'current_stage' => $this->whenLoaded('currentStage', fn () => [
                'id' => $this->currentStage->id,
                'name' => $this->currentStage->name,
                'order' => $this->currentStage->order,
            ]),
            'class_taken_at' => $this->class_taken_at?->toDateString(),
            'document_signed_at' => $this->document_signed_at?->toDateString(),
            'pastor_approved_at' => $this->pastor_approved_at?->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
