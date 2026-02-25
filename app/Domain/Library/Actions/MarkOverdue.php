<?php

namespace App\Domain\Library\Actions;

use App\Models\MaterialLoan;
use App\Support\Enums\LoanStatus;

class MarkOverdue
{
    public function handle(): int
    {
        return MaterialLoan::where('status', LoanStatus::Borrowed)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now()->toDateString())
            ->update(['status' => LoanStatus::Overdue]);
    }
}
