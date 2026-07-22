<?php

namespace App\Domain\People\Actions;

use App\Models\AttendanceRecord;
use App\Models\DoorReferral;
use App\Models\Person;
use Carbon\Carbon;

class BuildPersonContext
{
    /**
     * Build a structured snapshot of a person to feed to the AI routing engine.
     * Per project decision (2026-05-25): include full data (PII) for maximum precision.
     *
     * @return array<string, mixed>
     */
    public function handle(Person $person): array
    {
        $person->loadMissing([
            'membership.currentStage',
            'membershipHistory.toStage',
            'cells',
            'discipleshipAssignments.discipleship',
        ]);

        $birthDate = $person->birth_date;
        $age = $birthDate ? Carbon::parse($birthDate)->age : null;

        $eightWeeksAgo = now()->subWeeks(8);
        $attendanceLast8w = AttendanceRecord::where('person_id', $person->id)
            ->where('checked_in_at', '>=', $eightWeeksAgo)
            ->count();

        $openReferrals = DoorReferral::where('person_id', $person->id)
            ->whereIn('status', ['pending', 'in_progress', 'pending_review'])
            ->with('door')
            ->get();

        $closedReferrals = DoorReferral::where('person_id', $person->id)
            ->whereIn('status', ['completed', 'cancelled'])
            ->count();

        $activeCells = $person->cells()
            ->wherePivotNull('left_at')
            ->with('leader:id,first_name,last_name')
            ->get();

        $recentStages = $person->membershipHistory()
            ->with('toStage:id,name')
            ->orderByDesc('changed_at')
            ->limit(5)
            ->get();

        $discipleships = $person->discipleshipAssignments()
            ->with('discipleship:id,name')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return [
            'id' => $person->id,
            'full_name' => $person->full_name,
            'age' => $age,
            'gender' => $person->gender?->value,
            'marital_status' => $person->marital_status?->value,
            'status' => $person->status?->value,
            'status_label' => $person->status?->label(),
            'how_found_us' => $person->how_found_us?->value,
            'first_visit_date' => $person->first_visit_date?->toDateString(),
            'created_at' => $person->created_at?->toDateString(),
            'contact' => [
                'phone' => $person->phone,
                'email' => $person->email,
                'address' => array_filter([
                    $person->address_line1,
                    $person->city,
                    $person->state,
                ]),
            ],
            'notes_pastoral' => $person->notes_pastoral,
            'current_membership_stage' => $person->membership?->currentStage?->name,
            'recent_membership_changes' => $recentStages->map(fn ($h) => [
                'to_stage' => $h->toStage?->name,
                'changed_at' => $h->changed_at?->toDateString(),
            ])->all(),
            'active_cells' => $activeCells->map(fn ($c) => [
                'name' => $c->name,
                'leader' => $c->leader?->full_name,
            ])->all(),
            'attendance_last_8_weeks' => $attendanceLast8w,
            'recent_discipleships' => $discipleships->map(fn ($d) => [
                'name' => $d->discipleship?->name,
                'status' => $d->status?->value,
                'start_date' => $d->start_date?->toDateString(),
            ])->all(),
            'open_referrals' => $openReferrals->map(fn ($r) => [
                'door' => $r->door?->name,
                'category' => $r->category,
                'priority' => $r->priority?->value,
                'created_at' => $r->created_at?->toDateString(),
            ])->all(),
            'closed_referrals_count' => $closedReferrals,
            'recent_notes' => \App\Models\PersonNote::where('person_id', $person->id)
                ->latest('created_at')
                ->limit(10)
                ->get()
                ->map(fn ($n) => [
                    'type' => $n->type?->value,
                    'action' => $n->action_key,
                    'date' => $n->created_at?->toDateString(),
                    'body' => trim(strip_tags($n->body)),
                ])
                ->all(),
        ];
    }
}
