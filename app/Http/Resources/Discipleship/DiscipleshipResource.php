<?php

namespace App\Http\Resources\Discipleship;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscipleshipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'level' => $this->level?->value,
            'level_label' => $this->level?->label(),
            'duration_weeks' => $this->duration_weeks,
            'description' => $this->description,
            'leader' => $this->whenLoaded('leader', fn () => [
                'id' => $this->leader->id,
                'name' => $this->leader->name,
            ]),
            'assignments_count' => $this->whenCounted('assignments'),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
