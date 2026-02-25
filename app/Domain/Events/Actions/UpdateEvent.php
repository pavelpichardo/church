<?php

namespace App\Domain\Events\Actions;

use App\Models\Event;

class UpdateEvent
{
    public function handle(Event $event, array $data): Event
    {
        $event->update($data);

        return $event->fresh();
    }
}
