<?php

namespace App\Http\Requests\Doors;

use App\Support\Enums\DoorMemberRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDoorMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('door_members.manage');
    }

    public function rules(): array
    {
        return [
            'person_id' => ['required', 'exists:people,id'],
            'role' => ['required', Rule::enum(DoorMemberRole::class)],
            'joined_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
