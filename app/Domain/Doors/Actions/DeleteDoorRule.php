<?php

namespace App\Domain\Doors\Actions;

use App\Models\DoorRule;

class DeleteDoorRule
{
    public function handle(DoorRule $rule): void
    {
        $rule->delete();
    }
}
