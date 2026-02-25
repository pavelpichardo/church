<?php

namespace App\Support\Enums;

enum MessageChannel: string
{
    case Email = 'email';
    case Sms = 'sms';

    public function label(): string
    {
        return match($this) {
            self::Email => 'Correo Electrónico',
            self::Sms => 'SMS',
        };
    }
}
