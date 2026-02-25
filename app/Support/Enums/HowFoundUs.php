<?php

namespace App\Support\Enums;

enum HowFoundUs: string
{
    case Invited = 'invited';
    case SocialMedia = 'social_media';
    case WalkedIn = 'walked_in';
    case Transferred = 'transferred';
    case Other = 'other';

    public function label(): string
    {
        return match($this) {
            self::Invited => 'Invitado',
            self::SocialMedia => 'Redes Sociales',
            self::WalkedIn => 'Llegó solo',
            self::Transferred => 'Transferido',
            self::Other => 'Otro',
        };
    }
}
