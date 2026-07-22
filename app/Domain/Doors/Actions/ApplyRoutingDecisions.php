<?php

namespace App\Domain\Doors\Actions;

use App\Domain\AI\AnthropicClient;
use App\Models\Door;
use App\Models\DoorAiInference;
use App\Models\DoorAlert;
use App\Models\DoorReferral;
use App\Models\Person;
use App\Support\Enums\DoorAlertSeverity;
use App\Support\Enums\DoorReferralSource;
use App\Support\Enums\DoorReferralStatus;
use Illuminate\Support\Facades\DB;

class ApplyRoutingDecisions
{
    public function __construct(private readonly AnthropicClient $client)
    {
    }

    /**
     * Persist the AI inference audit row and create referrals/alerts from the decisions.
     *
     * @param  array<int, array<string, mixed>>  $decisions
     * @param  array<string, mixed>  $audit
     * @return array{inference: DoorAiInference, referrals: array<int, DoorReferral>, alerts: array<int, DoorAlert>}
     */
    public function handle(Person $person, array $decisions, array $audit, bool $dryRun = false): array
    {
        $threshold = $this->client->confidenceThreshold();

        DB::beginTransaction();

        try {
            $inference = DoorAiInference::create($audit);

            $referrals = [];
            $alerts = [];

            foreach ($decisions as $decision) {
                $door = Door::where('code', $decision['door_code'] ?? null)->first();
                if (! $door) {
                    continue;
                }

                $confidence = (float) ($decision['confidence'] ?? 1.0);
                $action = $decision['action'] ?? 'create_referral';
                $priority = $decision['priority'] ?? 'normal';
                $category = $decision['category'] ?? null;
                $reasoning = $decision['reasoning'] ?? null;
                $dueDays = isset($decision['due_days']) ? (int) $decision['due_days'] : null;
                $dueDate = $dueDays !== null ? now()->addDays($dueDays)->toDateString() : null;

                $referral = null;
                if ($action === 'create_referral') {
                    $referral = $this->makeReferral(
                        door: $door,
                        person: $person,
                        inference: $inference,
                        confidence: $confidence,
                        reasoning: $reasoning,
                        category: $category,
                        priority: $priority,
                        dueDate: $dueDate,
                        threshold: $threshold,
                    );
                    $referrals[] = $referral;
                }

                $alerts[] = $this->makeAlert(
                    door: $door,
                    referral: $referral,
                    category: (string) ($category ?? 'derivacion'),
                    reasoning: (string) ($reasoning ?? ''),
                    priority: (string) $priority,
                );
            }

            if ($dryRun) {
                DB::rollBack();
            } else {
                DB::commit();
            }

            return [
                'inference' => $inference,
                'referrals' => $referrals,
                'alerts' => $alerts,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function makeReferral(
        Door $door,
        Person $person,
        DoorAiInference $inference,
        float $confidence,
        ?string $reasoning,
        ?string $category,
        string $priority,
        ?string $dueDate,
        float $threshold,
    ): DoorReferral {
        $status = $confidence >= $threshold
            ? DoorReferralStatus::Pending->value
            : DoorReferralStatus::PendingReview->value;

        return DoorReferral::create([
            'door_id' => $door->id,
            'person_id' => $person->id,
            'source' => DoorReferralSource::Rule->value,
            'ai_inference_id' => $inference->id,
            'ai_confidence' => $confidence,
            'ai_reasoning' => $reasoning,
            'category' => $category,
            'priority' => $priority,
            'status' => $status,
            'due_date' => $dueDate,
        ])->refresh();
    }

    private function makeAlert(
        Door $door,
        ?DoorReferral $referral,
        string $category,
        string $reasoning,
        string $priority,
    ): DoorAlert {
        $severity = match ($priority) {
            'urgent' => DoorAlertSeverity::Critical->value,
            'high' => DoorAlertSeverity::Warning->value,
            default => DoorAlertSeverity::Info->value,
        };

        return DoorAlert::create([
            'door_id' => $door->id,
            'referral_id' => $referral?->id,
            'type' => "ai.{$category}",
            'message' => $reasoning,
            'severity' => $severity,
        ]);
    }
}
