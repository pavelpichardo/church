<?php

namespace App\Http\Requests\Doors;

use App\Support\Enums\DoorReferralPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDoorRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('door_rules.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string', 'min:10'],
            'event_types' => ['sometimes', 'nullable', 'array'],
            'event_types.*' => ['string', 'max:100'],
            'priority_hint' => ['sometimes', Rule::enum(DoorReferralPriority::class)],
            'is_enabled' => ['sometimes', 'boolean'],
        ];
    }
}
