<?php

namespace App\Http\Resources\Doors;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoorAlertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'door_id' => $this->door_id,
            'referral_id' => $this->referral_id,
            'type' => $this->type,
            'message' => $this->message,
            'severity' => $this->severity?->value,
            'severity_label' => $this->severity?->label(),
            'read_at' => $this->read_at?->toDateTimeString(),
            'is_read' => $this->read_at !== null,
            'referral' => new DoorReferralResource($this->whenLoaded('referral')),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
