<?php

namespace App\Domain\People\Actions;

use App\Models\Person;

class DeletePerson
{
    public function handle(Person $person): void
    {
        $person->delete();
    }
}
