<?php

namespace App\Support\Enums;

enum CongressTaskPhase: string
{
    case Before = 'before';
    case During = 'during';
    case After = 'after';

    public function label(): string
    {
        return match($this) {
            self::Before => 'Antes',
            self::During => 'Durante',
            self::After  => 'Después',
        };
    }
}
