<?php

namespace App\Support\Enums;

enum DiscipleshipLevel: string
{
    case Initial = 'initial';
    case Intermediate = 'intermediate';
    case Advanced = 'advanced';

    public function label(): string
    {
        return match($this) {
            self::Initial => 'Inicial',
            self::Intermediate => 'Intermedio',
            self::Advanced => 'Avanzado',
        };
    }
}
