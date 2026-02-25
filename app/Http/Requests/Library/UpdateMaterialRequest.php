<?php

namespace App\Http\Requests\Library;

use App\Support\Enums\MaterialType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('library.update');
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:255'],
            'material_type' => ['sometimes', Rule::enum(MaterialType::class)],
            'total_quantity' => ['sometimes', 'integer', 'min:0'],
            'available_quantity' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'file_id' => ['nullable', 'integer', 'exists:files,id'],
        ];
    }
}
