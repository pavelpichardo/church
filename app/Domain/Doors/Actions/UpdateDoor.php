<?php

namespace App\Domain\Doors\Actions;

use App\Models\Door;

class UpdateDoor
{
    public function handle(Door $door, array $data): Door
    {
        $door->update($data);

        return $door->fresh();
    }
}
