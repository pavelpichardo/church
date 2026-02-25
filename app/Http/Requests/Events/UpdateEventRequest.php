<?php

namespace App\Http\Requests\Events;

use App\Support\Enums\EventType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('events.update');
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'event_type' => ['sometimes', Rule::enum(EventType::class)],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['sometimes', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'is_recurring' => ['boolean'],
            'recurrence_rule' => ['nullable', 'string', 'max:255'],
        ];
    }
}
