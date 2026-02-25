<?php

namespace App\Http\Requests\Library;

use App\Support\Enums\MaterialType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('library.create');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:255'],
            'material_type' => ['required', Rule::enum(MaterialType::class)],
            'total_quantity' => ['required', 'integer', 'min:0'],
            'available_quantity' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'file_id' => ['nullable', 'integer', 'exists:files,id'],
        ];
    }
}
