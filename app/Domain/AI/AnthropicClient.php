<?php

namespace App\Domain\AI;

use Anthropic\Client as AnthropicSdkClient;

class AnthropicClient
{
    public function __construct(private readonly AnthropicSdkClient $sdk)
    {
    }

    public function sdk(): AnthropicSdkClient
    {
        return $this->sdk;
    }

    public function isEnabled(): bool
    {
        return (bool) config('services.anthropic.doors_ai_enabled')
            && filled(config('services.anthropic.api_key'));
    }

    public function defaultModel(): string
    {
        return (string) config('services.anthropic.default_model', 'claude-haiku-4-5');
    }

    public function confidenceThreshold(): float
    {
        return (float) config('services.anthropic.doors_ai_confidence_threshold', 0.85);
    }

    public function maxTokens(): int
    {
        return (int) config('services.anthropic.doors_ai_max_tokens', 1500);
    }

    /**
     * Estimate cost in USD for a Haiku 4.5 call given token usage.
     * Pricing: $1/1M input, $5/1M output, ~10% for cache reads, 125% for cache writes.
     */
    public function estimateCostUsd(int $promptTokens, int $outputTokens, int $cachedTokens = 0, int $cacheWriteTokens = 0): float
    {
        $inputCost = ($promptTokens / 1_000_000) * 1.00;
        $outputCost = ($outputTokens / 1_000_000) * 5.00;
        $cacheReadCost = ($cachedTokens / 1_000_000) * 0.10;
        $cacheWriteCost = ($cacheWriteTokens / 1_000_000) * 1.25;

        return round($inputCost + $outputCost + $cacheReadCost + $cacheWriteCost, 6);
    }
}
