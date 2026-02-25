<?php

namespace App\Domain\People\Actions;

use App\Models\Person;

class CreatePerson
{
    public function handle(array $data): Person
    {
        return Person::create($data);
    }
}
