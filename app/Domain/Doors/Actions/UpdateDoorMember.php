<?php

namespace App\Domain\Doors\Actions;

use App\Models\DoorMember;

class UpdateDoorMember
{
    public function handle(DoorMember $member, array $data): DoorMember
    {
        $member->update($data);

        return $member->fresh();
    }
}
