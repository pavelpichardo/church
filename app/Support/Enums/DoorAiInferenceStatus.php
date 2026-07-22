<?php

namespace App\Support\Enums;

enum DoorAiInferenceStatus: string
{
    case Success = 'success';
    case Failed = 'failed';
    case FallbackUsed = 'fallback_used';

    public function label(): string
    {
        return match ($this) {
            self::Success => 'Éxito',
            self::Failed => 'Falló',
            self::FallbackUsed => 'Fallback determinístico',
        };
    }
}
