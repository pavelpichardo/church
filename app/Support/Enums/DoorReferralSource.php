<?php

namespace App\Support\Enums;

enum DoorReferralSource: string
{
    case Manual = 'manual';
    case Cell = 'cell';
    case Rule = 'rule';
    case SelfRequest = 'self';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Manual',
            self::Cell => 'Líder de célula',
            self::Rule => 'Regla automática',
            self::SelfRequest => 'Auto-solicitud',
        };
    }
}
