<?php

namespace App\Http\Requests\Discipleship;

use Illuminate\Foundation\Http\FormRequest;

class AssignDiscipleshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('discipleship.assign');
    }

    public function rules(): array
    {
        return [
            'person_id' => ['required', 'integer', 'exists:people,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
