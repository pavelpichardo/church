<?php

namespace App\Support\Enums;

enum CommunicationStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Sending = 'sending';
    case Sent = 'sent';
    case Partial = 'partial';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Draft     => 'Borrador',
            self::Scheduled => 'Programado',
            self::Sending   => 'Enviando',
            self::Sent      => 'Enviado',
            self::Partial   => 'Enviado Parcial',
            self::Cancelled => 'Cancelado',
        };
    }
}
