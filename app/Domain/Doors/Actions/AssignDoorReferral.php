<?php

namespace App\Domain\Doors\Actions;

use App\Models\DoorReferral;
use App\Support\Enums\DoorReferralStatus;

class AssignDoorReferral
{
    public function handle(DoorReferral $referral, ?int $personId): DoorReferral
    {
        $attrs = ['assigned_to_person_id' => $personId];

        if ($personId !== null && $referral->status === DoorReferralStatus::Pending) {
            $attrs['status'] = DoorReferralStatus::InProgress;
        }

        $referral->update($attrs);

        return $referral->fresh();
    }
}
