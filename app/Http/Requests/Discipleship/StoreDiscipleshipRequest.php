<?php

namespace App\Http\Requests\Discipleship;

use App\Support\Enums\DiscipleshipLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDiscipleshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('discipleships.create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'level' => ['required', Rule::enum(DiscipleshipLevel::class)],
            'duration_weeks' => ['nullable', 'integer', 'min:1'],
            'leader_id' => ['nullable', 'integer', 'exists:users,id'],
            'description' => ['nullable', 'string'],
        ];
    }
}
