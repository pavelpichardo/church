<?php

namespace App\Http\Requests\Attendance;

use App\Support\Enums\CheckinMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('attendance.record');
    }

    public function rules(): array
    {
        return [
            'person_id' => ['required', 'integer', 'exists:people,id'],
            'checked_in_at' => ['nullable', 'date'],
            'checkin_method' => ['nullable', Rule::enum(CheckinMethod::class)],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
