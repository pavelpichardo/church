<?php

namespace App\Http\Resources\Doors;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code?->value,
            'code_label' => $this->code?->label(),
            'name' => $this->name,
            'order' => $this->order,
            'description' => $this->description,
            'color' => $this->color,
            'icon' => $this->icon,
            'is_active' => $this->is_active,
            'active_members_count' => $this->whenCounted('activeMembers'),
            'open_referrals_count' => $this->whenCounted('openReferrals'),
            'unread_alerts_count' => $this->whenCounted('unreadAlerts'),
            'rules' => DoorRuleResource::collection($this->whenLoaded('rules')),
            'leaders' => DoorMemberResource::collection($this->whenLoaded('leaders')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
