<?php

namespace App\Support\Enums;

enum DoorReferralPriority: string
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Baja',
            self::Normal => 'Normal',
            self::High => 'Alta',
            self::Urgent => 'Urgente',
        };
    }
}
