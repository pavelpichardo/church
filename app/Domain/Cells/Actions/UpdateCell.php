<?php

namespace App\Domain\Cells\Actions;

use App\Models\Cell;

class UpdateCell
{
    public function handle(Cell $cell, array $data): Cell
    {
        $cell->update($data);

        return $cell->fresh();
    }
}
