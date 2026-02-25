<?php

namespace App\Support\Enums;

enum LoanStatus: string
{
    case Borrowed = 'borrowed';
    case Returned = 'returned';
    case Lost = 'lost';
    case Overdue = 'overdue';

    public function label(): string
    {
        return match($this) {
            self::Borrowed => 'Prestado',
            self::Returned => 'Devuelto',
            self::Lost => 'Perdido',
            self::Overdue => 'Vencido',
        };
    }
}
