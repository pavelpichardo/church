<?php

namespace App\Domain\Doors\Actions;

use App\Models\Door;
use App\Models\DoorMember;

class AssignDoorMember
{
    public function handle(Door $door, array $data): DoorMember
    {
        $data['door_id'] = $door->id;
        $data['joined_at'] ??= now()->toDateString();

        return DoorMember::create($data);
    }
}
