<?php

namespace App\Http\Requests\Doors;

use Illuminate\Foundation\Http\FormRequest;

class RecordActivityAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('door_activities.manage');
    }

    public function rules(): array
    {
        return [
            'participants' => ['required', 'array', 'min:1'],
            'participants.*.person_id' => ['required', 'exists:people,id'],
            'participants.*.attended' => ['sometimes', 'boolean'],
            'participants.*.notes' => ['nullable', 'string'],
        ];
    }
}
