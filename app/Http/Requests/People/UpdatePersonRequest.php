<?php

namespace App\Http\Requests\People;

use App\Support\Enums\Gender;
use App\Support\Enums\HowFoundUs;
use App\Support\Enums\MaritalStatus;
use App\Support\Enums\PersonStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('people.update');
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name' => ['sometimes', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'marital_status' => ['nullable', Rule::enum(MaritalStatus::class)],
            'gender' => ['nullable', Rule::enum(Gender::class)],
            'how_found_us' => ['nullable', Rule::enum(HowFoundUs::class)],
            'first_visit_date' => ['nullable', 'date'],
            'status' => ['nullable', Rule::enum(PersonStatus::class)],
            'notes_pastoral' => ['nullable', 'string'],
            'photo_file_id' => ['nullable', 'integer', 'exists:files,id'],
        ];
    }
}
