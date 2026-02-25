<?php

namespace App\Support\Enums;

enum PersonStatus: string
{
    case Visitor = 'visitor';
    case MembershipProcess = 'membership_process';
    case Member = 'member';
    case ActiveMember = 'active_member';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match($this) {
            self::Visitor => 'Visitante',
            self::MembershipProcess => 'En Proceso',
            self::Member => 'Miembro',
            self::ActiveMember => 'Miembro Activo',
            self::Inactive => 'Inactivo',
        };
    }
}
