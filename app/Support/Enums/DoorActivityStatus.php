<?php

namespace App\Support\Enums;

enum DoorActivityStatus: string
{
    case Planned = 'planned';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Planificada',
            self::InProgress => 'En progreso',
            self::Completed => 'Completada',
            self::Cancelled => 'Cancelada',
        };
    }
}
