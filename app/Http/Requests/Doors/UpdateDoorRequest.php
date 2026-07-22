<?php

namespace App\Http\Requests\Doors;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDoorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('doors.manage');
    }

    public function rules(): array
    {
        return [
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:16'],
            'icon' => ['nullable', 'string', 'max:64'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
