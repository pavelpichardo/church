<?php

namespace App\Support\Enums;

enum MessageChannel: string
{
    case Email = 'email';
    case Sms = 'sms';
    case Whatsapp = 'whatsapp';

    public function label(): string
    {
        return match($this) {
            self::Email    => 'Correo Electrónico',
            self::Sms      => 'SMS',
            self::Whatsapp => 'WhatsApp',
        };
    }
}
