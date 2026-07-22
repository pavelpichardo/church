<?php

namespace App\Domain\Doors\Actions;

use App\Models\Door;
use App\Models\DoorRule;

class CreateDoorRule
{
    public function handle(Door $door, array $data): DoorRule
    {
        $data['door_id'] = $door->id;

        return DoorRule::create($data)->refresh();
    }
}
