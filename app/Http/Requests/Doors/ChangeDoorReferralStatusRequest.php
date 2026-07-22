<?php

namespace App\Http\Requests\Doors;

use App\Support\Enums\DoorReferralStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeDoorReferralStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('referrals.close')
            || $this->user()->can('referrals.review_pending');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(DoorReferralStatus::class)],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
