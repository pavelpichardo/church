<?php

namespace App\Domain\Library\Actions;

use App\Models\MaterialLoan;
use App\Models\Person;
use App\Models\StudyMaterial;
use App\Support\Enums\LoanStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoanMaterial
{
    public function handle(StudyMaterial $material, Person $person, array $data = []): MaterialLoan
    {
        if ($material->available_quantity < 1) {
            throw ValidationException::withMessages([
                'material' => 'No hay ejemplares disponibles de este material.',
            ]);
        }

        return DB::transaction(function () use ($material, $person, $data) {
            $loan = MaterialLoan::create([
                'study_material_id' => $material->id,
                'person_id' => $person->id,
                'assigned_by' => $data['assigned_by'] ?? Auth::id(),
                'assigned_at' => now(),
                'due_at' => $data['due_at'] ?? null,
                'status' => LoanStatus::Borrowed,
                'notes' => $data['notes'] ?? null,
            ]);

            $material->decrement('available_quantity');

            return $loan;
        });
    }
}
