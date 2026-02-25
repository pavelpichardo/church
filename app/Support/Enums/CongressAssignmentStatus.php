<?php

namespace App\Support\Enums;

enum CongressAssignmentStatus: string
{
    case Assigned = 'assigned';
    case Confirmed = 'confirmed';
    case Declined = 'declined';
    case Completed = 'completed';

    public function label(): string
    {
        return match($this) {
            self::Assigned => 'Asignado',
            self::Confirmed => 'Confirmado',
            self::Declined => 'Rechazado',
            self::Completed => 'Completado',
        };
    }
}
