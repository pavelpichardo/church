<?php

namespace App\Support\Enums;

enum MessageStatus: string
{
    case Queued = 'queued';
    case Sent = 'sent';
    case Failed = 'failed';

    public function label(): string
    {
        return match($this) {
            self::Queued => 'En Cola',
            self::Sent => 'Enviado',
            self::Failed => 'Fallido',
        };
    }
}
