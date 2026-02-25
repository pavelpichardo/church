<?php

namespace App\Http\Requests\Membership;

use Illuminate\Foundation\Http\FormRequest;

class AdvanceStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('membership.advance');
    }

    public function rules(): array
    {
        return [
            'stage_id' => ['required', 'integer', 'exists:membership_stages,id'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
