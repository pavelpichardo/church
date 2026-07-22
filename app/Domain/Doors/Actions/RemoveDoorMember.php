<?php

namespace App\Domain\Doors\Actions;

use App\Models\DoorMember;

class RemoveDoorMember
{
    public function handle(DoorMember $member): DoorMember
    {
        $member->update(['left_at' => now()->toDateString()]);

        return $member->fresh();
    }
}
