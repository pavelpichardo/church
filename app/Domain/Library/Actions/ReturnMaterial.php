<?php

namespace App\Domain\Library\Actions;

use App\Models\MaterialLoan;
use App\Support\Enums\LoanStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReturnMaterial
{
    public function handle(MaterialLoan $loan): MaterialLoan
    {
        if (!in_array($loan->status, [LoanStatus::Borrowed, LoanStatus::Overdue])) {
            throw ValidationException::withMessages([
                'loan' => 'Este préstamo no está activo.',
            ]);
        }

        return DB::transaction(function () use ($loan) {
            $loan->update([
                'status' => LoanStatus::Returned,
                'returned_at' => now(),
            ]);

            $loan->studyMaterial->increment('available_quantity');

            return $loan->fresh();
        });
    }
}
