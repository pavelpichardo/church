<?php

namespace App\Domain\Doors\Actions;

use App\Domain\AI\AnthropicClient;
use App\Domain\Doors\AI\RouteToDoorsTool;
use App\Domain\Doors\AI\RoutingPromptBuilder;
use App\Models\Person;
use App\Support\Enums\DoorAiInferenceStatus;
use RuntimeException;
use Throwable;

class InferDoorRouting
{
    public function __construct(
        private readonly AnthropicClient $client,
        private readonly RoutingPromptBuilder $promptBuilder,
    ) {
    }

    /**
     * Run inference. Returns an audit-ready array on success, throws on failure.
     *
     * @param  array<string, mixed>  $eventPayload
     * @param  array<string, mixed>  $personContext
     * @return array{decisions: array<int, array<string, mixed>>, audit: array<string, mixed>}
     */
    public function handle(
        string $eventType,
        array $eventPayload,
        Person $person,
        array $personContext,
    ): array {
        if (! $this->client->isEnabled()) {
            throw new RuntimeException('Doors AI is disabled (DOORS_AI_ENABLED=false or missing API key).');
        }

        $model = $this->client->defaultModel();
        $system = $this->promptBuilder->buildSystem();
        $userMessage = $this->promptBuilder->buildUserMessage($eventType, $eventPayload, $personContext);
        $tool = RouteToDoorsTool::definition();

        $start = microtime(true);

        try {
            $response = $this->client->sdk()->messages->create(
                maxTokens: $this->client->maxTokens(),
                messages: [
                    ['role' => 'user', 'content' => $userMessage],
                ],
                model: $model,
                system: $system,
                toolChoice: ['type' => 'tool', 'name' => RouteToDoorsTool::NAME],
                tools: [$tool],
            );
        } catch (Throwable $e) {
            $latencyMs = (int) ((microtime(true) - $start) * 1000);
            throw new RuntimeException(
                "Anthropic API call failed after {$latencyMs}ms: {$e->getMessage()}",
                previous: $e,
            );
        }

        $latencyMs = (int) ((microtime(true) - $start) * 1000);

        $decisions = $this->extractDecisions($response);
        $usage = $response->usage ?? null;

        $promptTokens = (int) ($usage?->inputTokens ?? 0);
        $outputTokens = (int) ($usage?->outputTokens ?? 0);
        $cachedTokens = (int) ($usage?->cacheReadInputTokens ?? 0);
        $cacheWriteTokens = (int) ($usage?->cacheCreationInputTokens ?? 0);

        $audit = [
            'triggering_event_type' => $eventType,
            'triggering_event_payload' => $eventPayload,
            'person_id' => $person->id,
            'model_used' => $model,
            'prompt_tokens' => $promptTokens,
            'cached_tokens' => $cachedTokens,
            'output_tokens' => $outputTokens,
            'cost_usd' => $this->client->estimateCostUsd($promptTokens, $outputTokens, $cachedTokens, $cacheWriteTokens),
            'raw_response' => $this->responseToArray($response),
            'decisions' => $decisions,
            'latency_ms' => $latencyMs,
            'status' => DoorAiInferenceStatus::Success->value,
        ];

        return ['decisions' => $decisions, 'audit' => $audit];
    }

    /**
     * Extract decisions from the tool_use block in the response.
     *
     * @return array<int, array<string, mixed>>
     */
    private function extractDecisions(mixed $response): array
    {
        foreach ($response->content ?? [] as $block) {
            $type = is_object($block) ? ($block->type ?? null) : ($block['type'] ?? null);
            if ($type !== 'tool_use') {
                continue;
            }
            $name = is_object($block) ? ($block->name ?? null) : ($block['name'] ?? null);
            if ($name !== RouteToDoorsTool::NAME) {
                continue;
            }
            $input = is_object($block) ? ($block->input ?? []) : ($block['input'] ?? []);
            if (is_object($input)) {
                $input = json_decode(json_encode($input), true);
            }
            return $input['decisions'] ?? [];
        }
        return [];
    }

    /**
     * Convert SDK response object to a plain array for storage.
     */
    private function responseToArray(mixed $response): array
    {
        $encoded = json_encode($response);
        return $encoded === false ? [] : (json_decode($encoded, true) ?? []);
    }
}
