<?php

namespace App\Domain\Doors\Actions;

use App\Models\DoorReferral;

class UpdateDoorReferral
{
    public function handle(DoorReferral $referral, array $data): DoorReferral
    {
        $referral->update($data);

        return $referral->fresh();
    }
}
