<?php

namespace App\Http\Requests\Doors;

use App\Support\Enums\DoorActivityStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDoorActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('door_activities.manage');
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'scheduled_at' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::enum(DoorActivityStatus::class)],
        ];
    }
}
