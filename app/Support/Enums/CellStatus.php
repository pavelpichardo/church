<?php

namespace App\Support\Enums;

enum CellStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Multiplied = 'multiplied';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Activa',
            self::Inactive => 'Inactiva',
            self::Multiplied => 'Multiplicada',
        };
    }
}
