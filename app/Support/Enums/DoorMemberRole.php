<?php

namespace App\Support\Enums;

enum DoorMemberRole: string
{
    case Leader = 'leader';
    case CoLeader = 'co_leader';
    case Volunteer = 'volunteer';

    public function label(): string
    {
        return match ($this) {
            self::Leader => 'Líder',
            self::CoLeader => 'Co-líder',
            self::Volunteer => 'Voluntario',
        };
    }
}
