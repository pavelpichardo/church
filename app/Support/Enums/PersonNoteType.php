<?php

namespace App\Support\Enums;

enum PersonNoteType: string
{
    case Note = 'note';
    case QuickAction = 'quick_action';
    case System = 'system';

    public function label(): string
    {
        return match ($this) {
            self::Note => 'Nota',
            self::QuickAction => 'Acción rápida',
            self::System => 'Sistema',
        };
    }
}
