<?php

namespace App\Domain\Discipleship\Actions;

use App\Models\DiscipleshipAssignment;
use App\Support\Enums\AssignmentStatus;
use Illuminate\Validation\ValidationException;

class CompleteDiscipleship
{
    public function handle(DiscipleshipAssignment $assignment): DiscipleshipAssignment
    {
        if ($assignment->status !== AssignmentStatus::InProgress) {
            throw ValidationException::withMessages([
                'assignment' => 'Solo se pueden completar asignaciones en progreso.',
            ]);
        }

        $assignment->update([
            'status' => AssignmentStatus::Completed,
            'end_date' => $assignment->end_date ?? now()->toDateString(),
        ]);

        return $assignment->fresh();
    }
}
