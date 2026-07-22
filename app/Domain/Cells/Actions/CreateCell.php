<?php

namespace App\Domain\Cells\Actions;

use App\Models\Cell;
use Illuminate\Support\Facades\Auth;

class CreateCell
{
    public function handle(array $data): Cell
    {
        $data['created_by'] ??= Auth::id();

        return Cell::create($data);
    }
}
