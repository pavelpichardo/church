<?php

namespace App\Domain\Doors\Actions;

use App\Models\DoorActivity;

class DeleteDoorActivity
{
    public function handle(DoorActivity $activity): void
    {
        $activity->delete();
    }
}
