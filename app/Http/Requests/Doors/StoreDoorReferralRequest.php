<?php

namespace App\Http\Requests\Doors;

use App\Support\Enums\DoorReferralPriority;
use App\Support\Enums\DoorReferralSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDoorReferralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('referrals.create');
    }

    public function rules(): array
    {
        return [
            'door_id' => ['required', 'exists:doors,id'],
            'person_id' => ['required', 'exists:people,id'],
            'source' => ['sometimes', Rule::enum(DoorReferralSource::class)],
            'source_cell_id' => ['nullable', 'exists:cells,id'],
            'category' => ['nullable', 'string', 'max:100'],
            'priority' => ['sometimes', Rule::enum(DoorReferralPriority::class)],
            'assigned_to_person_id' => ['nullable', 'exists:people,id'],
            'notes' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
