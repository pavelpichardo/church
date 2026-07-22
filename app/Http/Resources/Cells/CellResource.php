<?php

namespace App\Http\Resources\Cells;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CellResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'leader' => $this->whenLoaded('leader', fn () => [
                'id' => $this->leader->id,
                'full_name' => $this->leader->full_name,
            ]),
            'assistant' => $this->whenLoaded('assistant', fn () => $this->assistant ? [
                'id' => $this->assistant->id,
                'full_name' => $this->assistant->full_name,
            ] : null),
            'host' => $this->whenLoaded('host', fn () => $this->host ? [
                'id' => $this->host->id,
                'full_name' => $this->host->full_name,
            ] : null),
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'full_address' => $this->full_address,
            'max_capacity' => $this->max_capacity,
            'meeting_day' => $this->meeting_day?->value,
            'meeting_day_label' => $this->meeting_day?->label(),
            'meeting_time' => $this->meeting_time,
            'notes' => $this->notes,
            'members_count' => $this->whenCounted('activeMembers'),
            'parent_cell' => $this->whenLoaded('parentCell', fn () => $this->parentCell ? [
                'id' => $this->parentCell->id,
                'name' => $this->parentCell->name,
            ] : null),
            'child_cells_count' => $this->whenCounted('childCells'),
            'created_by' => $this->whenLoaded('createdBy', fn () => $this->createdBy ? [
                'id' => $this->createdBy->id,
                'name' => $this->createdBy->name,
            ] : null),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
