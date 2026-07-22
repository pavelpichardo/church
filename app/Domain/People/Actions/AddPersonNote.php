<?php

namespace App\Domain\People\Actions;

use App\Models\Person;
use App\Models\PersonNote;
use App\Support\Enums\PersonNoteType;
use Illuminate\Support\Facades\Auth;

class AddPersonNote
{
    public function handle(
        Person $person,
        string $body,
        PersonNoteType $type = PersonNoteType::Note,
        ?string $actionKey = null,
    ): PersonNote {
        return PersonNote::create([
            'person_id' => $person->id,
            'author_user_id' => Auth::id(),
            'type' => $type->value,
            'action_key' => $actionKey,
            'body' => $body,
        ])->refresh();
    }
}
