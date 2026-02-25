<?php

namespace App\Support\Enums;

enum CertificateType: string
{
    case Discipleship = 'discipleship';
    case Baptism = 'baptism';
    case Marriage = 'marriage';
    case Other = 'other';

    public function label(): string
    {
        return match($this) {
            self::Discipleship => 'Discipulado',
            self::Baptism => 'Bautismo',
            self::Marriage => 'Matrimonio',
            self::Other => 'Otro',
        };
    }
}
