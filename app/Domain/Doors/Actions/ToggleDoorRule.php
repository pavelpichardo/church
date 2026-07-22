<?php

namespace App\Domain\Doors\Actions;

use App\Models\DoorRule;

class ToggleDoorRule
{
    public function handle(DoorRule $rule): DoorRule
    {
        $rule->update(['is_enabled' => ! $rule->is_enabled]);

        return $rule->fresh();
    }
}
