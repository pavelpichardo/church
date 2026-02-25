<?php

namespace App\Domain\Events\Actions;

use App\Models\Event;
use Illuminate\Support\Facades\Auth;

class CreateEvent
{
    public function handle(array $data): Event
    {
        $data['created_by'] ??= Auth::id();

        return Event::create($data);
    }
}
