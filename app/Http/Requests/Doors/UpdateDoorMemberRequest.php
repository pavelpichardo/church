<?php

namespace App\Http\Requests\Doors;

use App\Support\Enums\DoorMemberRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDoorMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('door_members.manage');
    }

    public function rules(): array
    {
        return [
            'role' => ['sometimes', Rule::enum(DoorMemberRole::class)],
            'notes' => ['nullable', 'string'],
            'left_at' => ['nullable', 'date'],
        ];
    }
}
