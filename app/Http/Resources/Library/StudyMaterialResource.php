<?php

namespace App\Http\Resources\Library;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudyMaterialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'material_type' => $this->material_type?->value,
            'material_type_label' => $this->material_type?->label(),
            'total_quantity' => $this->total_quantity,
            'available_quantity' => $this->available_quantity,
            'description' => $this->description,
            'file_url' => $this->whenLoaded('file', fn () => $this->file?->url),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
