<?php

namespace App\Domain\Events\Actions;

use App\Events\CongressCreated;
use App\Models\Event;
use App\Support\Enums\EventType;
use Illuminate\Support\Facades\Auth;

class CreateEvent
{
    public function handle(array $data): Event
    {
        $data['created_by'] ??= Auth::id();

        $event = Event::create($data)->refresh();

        if ($event->event_type === EventType::Congress) {
            event(new CongressCreated(event: $event));
        }

        return $event;
    }
}
