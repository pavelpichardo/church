<?php

namespace App\Listeners;

use App\Events\Contracts\RoutingTriggerEvent;
use App\Jobs\EvaluateDoorRoutingForEvent;

class QueueDoorRoutingEvaluation
{
    public function handle(RoutingTriggerEvent $event): void
    {
        $personId = $event->personId();
        if ($personId <= 0) {
            return;
        }

        EvaluateDoorRoutingForEvent::dispatch(
            $event->eventType(),
            $event->toPayload(),
            $personId,
        );
    }
}
