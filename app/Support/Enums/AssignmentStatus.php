<?php

namespace App\Support\Enums;

enum AssignmentStatus: string
{
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::InProgress => 'En Progreso',
            self::Completed => 'Completado',
            self::Cancelled => 'Cancelado',
        };
    }
}
