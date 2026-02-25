<?php

namespace App\Http\Requests\Library;

use Illuminate\Foundation\Http\FormRequest;

class LoanMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('library.loan');
    }

    public function rules(): array
    {
        return [
            'person_id' => ['required', 'integer', 'exists:people,id'],
            'due_at' => ['nullable', 'date', 'after:today'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
