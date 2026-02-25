<?php

namespace App\Support\Enums;

enum MaritalStatus: string
{
    case Single = 'single';
    case Married = 'married';
    case Divorced = 'divorced';
    case Widowed = 'widowed';
    case Other = 'other';

    public function label(): string
    {
        return match($this) {
            self::Single => 'Soltero/a',
            self::Married => 'Casado/a',
            self::Divorced => 'Divorciado/a',
            self::Widowed => 'Viudo/a',
            self::Other => 'Otro',
        };
    }
}
