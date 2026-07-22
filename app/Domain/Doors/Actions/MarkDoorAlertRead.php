<?php

namespace App\Domain\Doors\Actions;

use App\Models\DoorAlert;

class MarkDoorAlertRead
{
    public function handle(DoorAlert $alert): DoorAlert
    {
        if ($alert->read_at === null) {
            $alert->update(['read_at' => now()]);
        }

        return $alert->fresh();
    }
}
