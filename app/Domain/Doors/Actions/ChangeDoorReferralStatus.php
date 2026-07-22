<?php

namespace App\Domain\Doors\Actions;

use App\Models\DoorReferral;
use App\Support\Enums\DoorReferralStatus;

class ChangeDoorReferralStatus
{
    public function handle(DoorReferral $referral, DoorReferralStatus $to, ?string $note = null): DoorReferral
    {
        $attrs = ['status' => $to];

        if ($to === DoorReferralStatus::Completed) {
            $attrs['completed_at'] = now();
        }

        if ($note) {
            $existing = trim((string) $referral->notes);
            $stamp = now()->toDateTimeString();
            $appended = "[{$stamp}] {$note}";
            $attrs['notes'] = $existing === '' ? $appended : ($existing . "\n\n" . $appended);
        }

        $referral->update($attrs);

        return $referral->fresh();
    }
}
