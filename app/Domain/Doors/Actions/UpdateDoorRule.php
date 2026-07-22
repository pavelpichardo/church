<?php

namespace App\Domain\Doors\Actions;

use App\Models\DoorRule;

class UpdateDoorRule
{
    public function handle(DoorRule $rule, array $data): DoorRule
    {
        $rule->update($data);

        return $rule->fresh();
    }
}
