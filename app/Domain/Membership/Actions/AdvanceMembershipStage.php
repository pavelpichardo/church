<?php

namespace App\Domain\Membership\Actions;

use App\Models\MembershipStage;
use App\Models\MembershipStageHistory;
use App\Models\Person;
use App\Models\PersonMembership;
use App\Support\Enums\PersonStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdvanceMembershipStage
{
    public function handle(Person $person, int $toStageId, ?string $note = null): PersonMembership
    {
        $toStage = MembershipStage::findOrFail($toStageId);

        $membership = $person->membership ?? PersonMembership::create([
            'person_id' => $person->id,
            'current_stage_id' => $toStageId,
        ]);

        $fromStageId = $membership->current_stage_id !== $toStageId
            ? $membership->current_stage_id
            : null;

        if ($fromStageId === $toStageId) {
            throw ValidationException::withMessages([
                'stage' => 'La persona ya se encuentra en esta etapa.',
            ]);
        }

        MembershipStageHistory::create([
            'person_id' => $person->id,
            'from_stage_id' => $fromStageId,
            'to_stage_id' => $toStageId,
            'changed_by' => Auth::id(),
            'changed_at' => now(),
            'note' => $note,
        ]);

        $membership->update(['current_stage_id' => $toStageId]);

        // Update person status based on stage name
        $statusMap = [
            'Visitante' => PersonStatus::Visitor,
            'Clase' => PersonStatus::MembershipProcess,
            'Firma' => PersonStatus::MembershipProcess,
            'Aprobación' => PersonStatus::MembershipProcess,
            'Miembro' => PersonStatus::Member,
            'Miembro Activo' => PersonStatus::ActiveMember,
        ];

        if (isset($statusMap[$toStage->name])) {
            $person->update(['status' => $statusMap[$toStage->name]]);
        }

        return $membership->fresh('currentStage');
    }
}
