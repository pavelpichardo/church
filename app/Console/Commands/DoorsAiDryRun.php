<?php

namespace App\Console\Commands;

use App\Domain\AI\AnthropicClient;
use App\Domain\Doors\Actions\ApplyRoutingDecisions;
use App\Domain\Doors\Actions\FallbackRouting;
use App\Domain\Doors\Actions\InferDoorRouting;
use App\Domain\People\Actions\BuildPersonContext;
use App\Models\Person;
use Illuminate\Console\Command;
use Throwable;

class DoorsAiDryRun extends Command
{
    protected $signature = 'doors:ai:dry-run
        {person_id : ID of the person to test routing for}
        {--event=person.registered : Event type to simulate}
        {--apply : Persist the decisions (default: dry-run, rolls back)}
        {--fallback : Force fallback path (skip Claude API)}';

    protected $description = 'Test the doors AI routing engine against a specific person without affecting production data.';

    public function handle(
        AnthropicClient $client,
        BuildPersonContext $contextBuilder,
        InferDoorRouting $inferRouting,
        FallbackRouting $fallback,
        ApplyRoutingDecisions $apply,
    ): int {
        $personId = (int) $this->argument('person_id');
        $eventType = (string) $this->option('event');
        $dryRun = ! $this->option('apply');
        $forceFallback = (bool) $this->option('fallback');

        $person = Person::find($personId);
        if (! $person) {
            $this->error("Person {$personId} not found.");
            return self::FAILURE;
        }

        $this->info("Testing routing for: {$person->full_name} (#{$person->id})");
        $this->info("Event: {$eventType}");
        $this->info("Mode: " . ($dryRun ? 'DRY-RUN (no changes will persist)' : 'APPLY (changes will persist)'));
        $this->newLine();

        $context = $contextBuilder->handle($person);

        try {
            if ($forceFallback || ! $client->isEnabled()) {
                if ($forceFallback) {
                    $this->warn('Forced fallback path.');
                } elseif (! $client->isEnabled()) {
                    $this->warn('AI disabled (DOORS_AI_ENABLED=false or no API key) — using fallback.');
                }
                $result = $fallback->handle($eventType, [], $person, $context);
            } else {
                $this->info('Calling Claude API...');
                $result = $inferRouting->handle($eventType, [], $person, $context);
                $audit = $result['audit'];
                $this->info("  → model: {$audit['model_used']}");
                $this->info("  → latency: {$audit['latency_ms']}ms");
                $this->info("  → tokens: prompt={$audit['prompt_tokens']} (cached={$audit['cached_tokens']}) output={$audit['output_tokens']}");
                $this->info("  → cost: \${$audit['cost_usd']}");
            }
        } catch (Throwable $e) {
            $this->error("Inference failed: {$e->getMessage()}");
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('=== DECISIONES ===');
        if (empty($result['decisions'])) {
            $this->line('  (sin acción)');
        } else {
            foreach ($result['decisions'] as $i => $d) {
                $threshold = $client->confidenceThreshold();
                $status = ($d['confidence'] ?? 1) >= $threshold ? 'auto' : 'pending_review';
                $this->line(sprintf(
                    '  [%d] %s → %s | %s | conf=%.2f (%s)',
                    $i + 1,
                    $d['door_code'] ?? '?',
                    $d['action'] ?? '?',
                    $d['priority'] ?? '?',
                    $d['confidence'] ?? 0,
                    $status,
                ));
                $this->line("       categoría: {$d['category']}");
                $this->line("       razón: {$d['reasoning']}");
            }
        }

        $this->newLine();
        $applied = $apply->handle($person, $result['decisions'], $result['audit'], dryRun: $dryRun);

        if ($dryRun) {
            $this->warn('DRY-RUN: changes rolled back. Re-run with --apply to persist.');
        } else {
            $this->info(sprintf(
                'Applied: %d referral(s), %d alert(s), inference #%d',
                count($applied['referrals']),
                count($applied['alerts']),
                $applied['inference']->id,
            ));
        }

        return self::SUCCESS;
    }
}
