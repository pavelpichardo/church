<?php

namespace App\Support\Enums;

enum TriggerType: string
{
    case Birthday = 'birthday';
    case EventReminder = 'event_reminder';
    case DiscipleshipFollowup = 'discipleship_followup';
    case MembershipStep = 'membership_step';
    case Manual = 'manual';

    public function label(): string
    {
        return match($this) {
            self::Birthday => 'Cumpleaños',
            self::EventReminder => 'Recordatorio de Evento',
            self::DiscipleshipFollowup => 'Seguimiento de Discipulado',
            self::MembershipStep => 'Paso de Membresía',
            self::Manual => 'Manual',
        };
    }
}
