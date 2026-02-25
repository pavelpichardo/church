<?php

namespace App\Http\Requests\Attendance;

use App\Support\Enums\CheckinMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('attendance.record');
    }

    public function rules(): array
    {
        return [
            'person_ids' => ['required', 'array', 'min:1'],
            'person_ids.*' => ['integer', 'exists:people,id'],
            'checkin_method' => ['nullable', Rule::enum(CheckinMethod::class)],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
