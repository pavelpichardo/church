<?php

namespace App\Http\Requests\Doors;

use Illuminate\Foundation\Http\FormRequest;

class AssignDoorReferralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('referrals.assign');
    }

    public function rules(): array
    {
        return [
            'person_id' => ['nullable', 'exists:people,id'],
        ];
    }
}
