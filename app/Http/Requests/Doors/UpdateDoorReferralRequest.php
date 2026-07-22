<?php

namespace App\Http\Requests\Doors;

use App\Support\Enums\DoorReferralPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDoorReferralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('referrals.create');
    }

    public function rules(): array
    {
        return [
            'category' => ['sometimes', 'nullable', 'string', 'max:100'],
            'priority' => ['sometimes', Rule::enum(DoorReferralPriority::class)],
            'notes' => ['sometimes', 'nullable', 'string'],
            'due_date' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
