<?php

namespace App\Domain\Doors\Actions;

use App\Models\DoorActivity;

class UpdateDoorActivity
{
    public function handle(DoorActivity $activity, array $data): DoorActivity
    {
        $activity->update($data);

        return $activity->fresh();
    }
}
