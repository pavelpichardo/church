<?php

namespace App\Jobs;

use App\Domain\AI\AnthropicClient;
use App\Domain\Doors\Actions\ApplyRoutingDecisions;
use App\Domain\Doors\Actions\FallbackRouting;
use App\Domain\Doors\Actions\InferDoorRouting;
use App\Domain\People\Actions\BuildPersonContext;
use App\Models\Person;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class EvaluateDoorRoutingForEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 8;

    public function __construct(
        public readonly string $eventType,
        public readonly array $eventPayload,
        public readonly int $personId,
    ) {
        $this->onQueue('doors-ai');
    }

    public function handle(
        AnthropicClient $client,
        BuildPersonContext $contextBuilder,
        InferDoorRouting $inferRouting,
        FallbackRouting $fallback,
        ApplyRoutingDecisions $apply,
    ): void {
        $person = Person::find($this->personId);
        if (! $person) {
            Log::warning("Door routing skipped: person {$this->personId} not found.");
            return;
        }

        $context = $contextBuilder->handle($person);

        if (! $client->isEnabled()) {
            $result = $fallback->handle(
                $this->eventType,
                $this->eventPayload,
                $person,
                $context,
                errorMessage: 'AI disabled — using deterministic fallback.',
            );
            $apply->handle($person, $result['decisions'], $result['audit']);
            return;
        }

        try {
            $result = $inferRouting->handle($this->eventType, $this->eventPayload, $person, $context);
        } catch (Throwable $e) {
            Log::warning("Door routing AI failed, using fallback: {$e->getMessage()}", [
                'event_type' => $this->eventType,
                'person_id' => $this->personId,
            ]);

            $result = $fallback->handle(
                $this->eventType,
                $this->eventPayload,
                $person,
                $context,
                errorMessage: $e->getMessage(),
            );
        }

        $apply->handle($person, $result['decisions'], $result['audit']);
    }

    public function failed(Throwable $exception): void
    {
        Log::error("EvaluateDoorRoutingForEvent failed permanently: {$exception->getMessage()}", [
            'event_type' => $this->eventType,
            'person_id' => $this->personId,
        ]);
    }
}
