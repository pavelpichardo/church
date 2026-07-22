<?php

namespace App\Domain\Doors\Actions;

use App\Models\Door;
use App\Models\DoorActivity;
use Illuminate\Support\Facades\Auth;

class CreateDoorActivity
{
    public function handle(Door $door, array $data): DoorActivity
    {
        $data['door_id'] = $door->id;
        $data['created_by'] ??= Auth::id();

        return DoorActivity::create($data)->refresh();
    }
}
