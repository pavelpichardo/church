<?php

namespace App\Domain\Discipleship\Actions;

use App\Models\Discipleship;
use App\Models\DiscipleshipAssignment;
use App\Models\Person;
use App\Support\Enums\AssignmentStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AssignDiscipleship
{
    public function handle(Discipleship $discipleship, Person $person, array $data = []): DiscipleshipAssignment
    {
        $existing = DiscipleshipAssignment::where('discipleship_id', $discipleship->id)
            ->where('person_id', $person->id)
            ->where('status', AssignmentStatus::InProgress)
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'assignment' => 'Esta persona ya tiene este discipulado en progreso.',
            ]);
        }

        return DiscipleshipAssignment::create([
            'discipleship_id' => $discipleship->id,
            'person_id' => $person->id,
            'assigned_by' => $data['assigned_by'] ?? Auth::id(),
            'start_date' => $data['start_date'] ?? now()->toDateString(),
            'end_date' => $data['end_date'] ?? null,
            'status' => AssignmentStatus::InProgress,
            'notes' => $data['notes'] ?? null,
        ]);
    }
}
