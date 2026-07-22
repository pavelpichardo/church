<?php

namespace App\Support\Enums;

enum DoorAlertSeverity: string
{
    case Info = 'info';
    case Warning = 'warning';
    case Critical = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::Info => 'Informativa',
            self::Warning => 'Advertencia',
            self::Critical => 'Crítica',
        };
    }
}
