<?php

namespace App\Http\Resources\People;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'birth_date' => $this->birth_date?->toDateString(),
            'marital_status' => $this->marital_status?->value,
            'gender' => $this->gender?->value,
            'how_found_us' => $this->how_found_us?->value,
            'first_visit_date' => $this->first_visit_date?->toDateString(),
            'status' => $this->status?->value,
            'notes_pastoral' => $this->when(
                $request->user()?->can('people.update'),
                $this->notes_pastoral
            ),
            'photo_url' => $this->whenLoaded('photo', fn () => $this->photo?->url),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
