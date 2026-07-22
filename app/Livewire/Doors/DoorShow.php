<?php

namespace App\Livewire\Doors;

use App\Domain\AI\AnthropicClient;
use App\Domain\Doors\Actions\ApplyRoutingDecisions;
use App\Domain\Doors\Actions\AssignDoorMember;
use App\Domain\Doors\Actions\ChangeDoorReferralStatus;
use App\Domain\Doors\Actions\CreateDoorRule;
use App\Domain\Doors\Actions\DeleteDoorRule;
use App\Domain\Doors\Actions\FallbackRouting;
use App\Domain\Doors\Actions\InferDoorRouting;
use App\Domain\Doors\Actions\MarkDoorAlertRead;
use App\Domain\Doors\Actions\RemoveDoorMember;
use App\Domain\Doors\Actions\ToggleDoorRule;
use App\Domain\Doors\Actions\UpdateDoorRule;
use App\Domain\People\Actions\BuildPersonContext;
use App\Models\Door;
use App\Models\DoorAlert;
use App\Models\DoorMember;
use App\Models\DoorReferral;
use App\Models\DoorRule;
use App\Models\Person;
use App\Support\Enums\DoorMemberRole;
use App\Support\Enums\DoorReferralPriority;
use App\Support\Enums\DoorReferralStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Throwable;

#[Layout('components.layouts.app')]
class DoorShow extends Component
{
    public Door $door;

    #[Url(as: 'tab')]
    public string $activeTab = 'team';

    // Rule edit modal state
    public bool $showRuleModal = false;
    public ?int $editingRuleId = null;
    public string $ruleName = '';
    public string $ruleDescription = '';
    public string $rulePriorityHint = 'normal';
    public string $ruleEventTypes = '';
    public bool $ruleEnabled = true;

    // Member add modal
    public bool $showMemberModal = false;
    public string $memberPersonId = '';
    public string $memberRole = 'volunteer';

    // Dry-run modal
    public bool $showDryRunModal = false;
    public string $dryRunPersonId = '';
    public string $dryRunEventType = 'person.registered';
    public bool $dryRunUseFallback = false;
    public ?array $dryRunResult = null;
    public ?string $dryRunError = null;

    public function mount(Door $door): void
    {
        $this->door = $door;
    }

    // ─── Rules CRUD ─────────────────────────────────────────────────────────

    public function openCreateRule(): void
    {
        abort_unless(Gate::allows('door_rules.manage'), 403);
        $this->resetValidation();
        $this->editingRuleId = null;
        $this->ruleName = '';
        $this->ruleDescription = '';
        $this->rulePriorityHint = 'normal';
        $this->ruleEventTypes = '';
        $this->ruleEnabled = true;
        $this->showRuleModal = true;
    }

    public function openEditRule(int $ruleId): void
    {
        abort_unless(Gate::allows('door_rules.manage'), 403);
        $rule = DoorRule::where('door_id', $this->door->id)->findOrFail($ruleId);
        $this->resetValidation();
        $this->editingRuleId = $ruleId;
        $this->ruleName = $rule->name;
        $this->ruleDescription = $rule->description;
        $this->rulePriorityHint = $rule->priority_hint?->value ?? 'normal';
        $this->ruleEventTypes = implode(', ', $rule->event_types ?? []);
        $this->ruleEnabled = (bool) $rule->is_enabled;
        $this->showRuleModal = true;
    }

    public function saveRule(): void
    {
        $data = $this->validate([
            'ruleName' => 'required|string|max:255',
            'ruleDescription' => 'required|string|min:10',
            'rulePriorityHint' => 'required|in:low,normal,high,urgent',
            'ruleEventTypes' => 'nullable|string',
            'ruleEnabled' => 'boolean',
        ]);

        $eventTypes = collect(explode(',', $data['ruleEventTypes'] ?? ''))
            ->map(fn ($e) => trim($e))
            ->filter()
            ->values()
            ->all();

        $payload = [
            'name' => $data['ruleName'],
            'description' => $data['ruleDescription'],
            'event_types' => empty($eventTypes) ? null : $eventTypes,
            'priority_hint' => $data['rulePriorityHint'],
            'is_enabled' => $data['ruleEnabled'],
        ];

        if ($this->editingRuleId) {
            $rule = DoorRule::where('door_id', $this->door->id)->findOrFail($this->editingRuleId);
            (new UpdateDoorRule())->handle($rule, $payload);
            session()->flash('success', 'Regla actualizada.');
        } else {
            (new CreateDoorRule())->handle($this->door, $payload);
            session()->flash('success', 'Regla creada.');
        }

        $this->showRuleModal = false;
    }

    public function addEventType(string $slug): void
    {
        $current = collect(explode(',', $this->ruleEventTypes))
            ->map(fn ($e) => trim($e))
            ->filter();

        if (! $current->contains($slug)) {
            $current->push($slug);
        }

        $this->ruleEventTypes = $current->implode(', ');
    }

    public function clearEventTypes(): void
    {
        $this->ruleEventTypes = '';
    }

    public function toggleRule(int $ruleId): void
    {
        abort_unless(Gate::allows('door_rules.manage'), 403);
        $rule = DoorRule::where('door_id', $this->door->id)->findOrFail($ruleId);
        (new ToggleDoorRule())->handle($rule);
    }

    public function deleteRule(int $ruleId): void
    {
        abort_unless(Gate::allows('door_rules.manage'), 403);
        $rule = DoorRule::where('door_id', $this->door->id)->findOrFail($ruleId);
        (new DeleteDoorRule())->handle($rule);
        session()->flash('success', 'Regla eliminada.');
    }

    // ─── Members ────────────────────────────────────────────────────────────

    public function openAddMember(): void
    {
        abort_unless(Gate::allows('door_members.manage'), 403);
        $this->memberPersonId = '';
        $this->memberRole = 'volunteer';
        $this->showMemberModal = true;
    }

    public function addMember(): void
    {
        $data = $this->validate([
            'memberPersonId' => 'required|exists:people,id',
            'memberRole' => 'required|in:leader,co_leader,volunteer',
        ]);

        (new AssignDoorMember())->handle($this->door, [
            'person_id' => (int) $data['memberPersonId'],
            'role' => $data['memberRole'],
        ]);

        $this->showMemberModal = false;
        session()->flash('success', 'Voluntario asignado.');
    }

    public function removeMember(int $memberId): void
    {
        abort_unless(Gate::allows('door_members.manage'), 403);
        $member = DoorMember::where('door_id', $this->door->id)->findOrFail($memberId);
        (new RemoveDoorMember())->handle($member);
        session()->flash('success', 'Voluntario removido.');
    }

    // ─── Referrals ──────────────────────────────────────────────────────────

    public function startReferral(int $referralId): void
    {
        abort_unless(Gate::allows('referrals.assign'), 403);
        $referral = DoorReferral::where('door_id', $this->door->id)->findOrFail($referralId);
        (new ChangeDoorReferralStatus())->handle($referral, DoorReferralStatus::InProgress);
    }

    public function completeReferral(int $referralId): void
    {
        abort_unless(Gate::allows('referrals.close'), 403);
        $referral = DoorReferral::where('door_id', $this->door->id)->findOrFail($referralId);
        (new ChangeDoorReferralStatus())->handle($referral, DoorReferralStatus::Completed, 'Cerrada desde panel');
        session()->flash('success', 'Derivación completada.');
    }

    public function approveReferral(int $referralId): void
    {
        abort_unless(Gate::allows('referrals.review_pending'), 403);
        $referral = DoorReferral::where('door_id', $this->door->id)->findOrFail($referralId);
        (new ChangeDoorReferralStatus())->handle($referral, DoorReferralStatus::Pending, 'Aprobada por líder');
        session()->flash('success', 'Sugerencia de IA aprobada.');
    }

    public function rejectReferral(int $referralId): void
    {
        abort_unless(Gate::allows('referrals.review_pending'), 403);
        $referral = DoorReferral::where('door_id', $this->door->id)->findOrFail($referralId);
        (new ChangeDoorReferralStatus())->handle($referral, DoorReferralStatus::Cancelled, 'Rechazada por líder');
        session()->flash('success', 'Sugerencia rechazada.');
    }

    // ─── Alerts ─────────────────────────────────────────────────────────────

    public function markAlertRead(int $alertId): void
    {
        abort_unless(Gate::allows('door_alerts.manage'), 403);
        $alert = DoorAlert::where('door_id', $this->door->id)->findOrFail($alertId);
        (new MarkDoorAlertRead())->handle($alert);
    }

    public function markAllAlertsRead(): void
    {
        abort_unless(Gate::allows('door_alerts.manage'), 403);
        $this->door->alerts()->whereNull('read_at')->update(['read_at' => now()]);
        session()->flash('success', 'Alertas marcadas como leídas.');
    }

    // ─── Dry-run ────────────────────────────────────────────────────────────

    public function openDryRun(): void
    {
        abort_unless(Gate::allows('door_rules.manage'), 403);
        $this->dryRunPersonId = '';
        $this->dryRunEventType = 'person.registered';
        $this->dryRunUseFallback = false;
        $this->dryRunResult = null;
        $this->dryRunError = null;
        $this->showDryRunModal = true;
    }

    public function runDryRun(
        AnthropicClient $client,
        BuildPersonContext $contextBuilder,
        InferDoorRouting $inferRouting,
        FallbackRouting $fallback,
        ApplyRoutingDecisions $apply,
    ): void {
        $this->dryRunResult = null;
        $this->dryRunError = null;

        $this->validate([
            'dryRunPersonId' => 'required|exists:people,id',
            'dryRunEventType' => 'required|string',
        ]);

        $person = Person::find((int) $this->dryRunPersonId);
        $context = $contextBuilder->handle($person);

        try {
            if ($this->dryRunUseFallback || ! $client->isEnabled()) {
                $result = $fallback->handle($this->dryRunEventType, [], $person, $context, errorMessage: 'Dry-run forzado a fallback.');
            } else {
                $result = $inferRouting->handle($this->dryRunEventType, [], $person, $context);
            }
            // Use the action in dry-run mode to validate persistence path without writing data.
            $apply->handle($person, $result['decisions'], $result['audit'], dryRun: true);
            $this->dryRunResult = [
                'decisions' => $result['decisions'],
                'audit' => $result['audit'],
            ];
        } catch (Throwable $e) {
            $this->dryRunError = $e->getMessage();
        }
    }

    // ─── Render ─────────────────────────────────────────────────────────────

    public function render()
    {
        $door = $this->door->loadCount(['activeMembers', 'openReferrals', 'unreadAlerts']);

        $members = $door->activeMembers()
            ->with('person')
            ->orderByRaw("CASE role WHEN 'leader' THEN 1 WHEN 'co_leader' THEN 2 WHEN 'volunteer' THEN 3 ELSE 4 END")
            ->get();
        $rules = $door->rules()->orderBy('name')->get();
        $referrals = $door->referrals()
            ->whereIn('status', ['pending', 'in_progress', 'pending_review'])
            ->with(['person', 'assignedTo', 'aiInference'])
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'normal' THEN 3 WHEN 'low' THEN 4 ELSE 5 END")
            ->orderByDesc('created_at')
            ->get();
        $activities = $door->activities()->orderByDesc('scheduled_at')->limit(20)->get();
        $alerts = $door->alerts()->with('referral.person')->orderByDesc('created_at')->limit(50)->get();

        // Simple metrics for the Reports tab
        $closedCount = $door->referrals()->where('status', 'completed')->count();
        $aiCount = $door->referrals()->whereNotNull('ai_inference_id')->count();
        $manualCount = $door->referrals()->whereNull('ai_inference_id')->count();
        $totalCost = \App\Models\DoorAiInference::whereJsonContains('decisions', [['door_code' => $door->code->value]])
            ->sum('cost_usd');

        $people = Person::orderBy('first_name')->limit(500)->get(['id', 'first_name', 'last_name']);
        $priorities = DoorReferralPriority::cases();
        $memberRoles = DoorMemberRole::cases();
        $eventCatalog = \App\Domain\Doors\RoutingEventCatalog::all();

        return view('livewire.doors.show', compact(
            'door',
            'members',
            'rules',
            'referrals',
            'activities',
            'alerts',
            'closedCount',
            'aiCount',
            'manualCount',
            'totalCost',
            'people',
            'priorities',
            'memberRoles',
            'eventCatalog',
        ));
    }
}
