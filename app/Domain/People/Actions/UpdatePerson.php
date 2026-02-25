<?php

namespace App\Domain\People\Actions;

use App\Models\Person;

class UpdatePerson
{
    public function handle(Person $person, array $data): Person
    {
        $person->update($data);

        return $person->fresh();
    }
}
