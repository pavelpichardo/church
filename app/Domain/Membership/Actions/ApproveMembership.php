<?php

namespace App\Domain\Membership\Actions;

use App\Models\MembershipStage;
use App\Models\Person;
use App\Models\PersonMembership;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ApproveMembership
{
    public function handle(Person $person): PersonMembership
    {
        $membership = $person->membership;

        if (!$membership) {
            throw ValidationException::withMessages([
                'membership' => 'Esta persona no tiene un proceso de membresía activo.',
            ]);
        }

        $approvalStage = MembershipStage::where('name', 'Aprobación')->first();

        if (!$approvalStage || $membership->current_stage_id !== $approvalStage->id) {
            throw ValidationException::withMessages([
                'membership' => 'La persona debe estar en la etapa de Aprobación para ser aprobada.',
            ]);
        }

        $membership->update([
            'pastor_approved_at' => now(),
            'pastor_approved_by' => Auth::id(),
        ]);

        // Advance to Member stage
        $memberStage = MembershipStage::where('name', 'Miembro')->first();
        if ($memberStage) {
            app(AdvanceMembershipStage::class)->handle($person, $memberStage->id, 'Aprobado por el pastor');
        }

        return $membership->fresh();
    }
}
