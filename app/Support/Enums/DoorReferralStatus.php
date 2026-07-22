<?php

namespace App\Support\Enums;

enum DoorReferralStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case PendingReview = 'pending_review';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::InProgress => 'En progreso',
            self::PendingReview => 'Esperando revisión',
            self::Completed => 'Completada',
            self::Cancelled => 'Cancelada',
        };
    }
}
