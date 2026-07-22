<?php

namespace App\Domain\Doors\Actions;

use App\Models\DoorReferral;
use App\Support\Enums\DoorReferralSource;
use Illuminate\Support\Facades\Auth;

class CreateDoorReferral
{
    public function handle(array $data): DoorReferral
    {
        $data['source'] ??= DoorReferralSource::Manual->value;
        $data['source_user_id'] ??= Auth::id();

        return DoorReferral::create($data)->refresh();
    }
}
