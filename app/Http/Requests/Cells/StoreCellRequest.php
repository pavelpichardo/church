<?php

namespace App\Http\Requests\Cells;

use App\Support\Enums\CellStatus;
use App\Support\Enums\DayOfWeek;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCellRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('cells.create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'leader_id' => ['required', 'exists:people,id'],
            'assistant_id' => ['nullable', 'exists:people,id'],
            'host_id' => ['nullable', 'exists:people,id'],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'status' => ['sometimes', Rule::enum(CellStatus::class)],
            'max_capacity' => ['sometimes', 'integer', 'min:2', 'max:50'],
            'meeting_day' => ['nullable', Rule::enum(DayOfWeek::class)],
            'meeting_time' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
