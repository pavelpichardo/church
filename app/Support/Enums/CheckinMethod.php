<?php

namespace App\Support\Enums;

enum CheckinMethod: string
{
    case Manual = 'manual';
    case Quick = 'quick';
    case Qr = 'qr';
    case Import = 'import';

    public function label(): string
    {
        return match($this) {
            self::Manual => 'Manual',
            self::Quick => 'Rápido',
            self::Qr => 'QR',
            self::Import => 'Importación',
        };
    }
}
