<?php

namespace App\Domain\People\Actions;

use App\Events\PersonRegistered;
use App\Models\Person;

class CreatePerson
{
    public function handle(array $data): Person
    {
        $person = Person::create($data)->refresh();

        event(new PersonRegistered($person));

        return $person;
    }
}
