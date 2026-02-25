<?php

namespace App\Support\Enums;

enum EventType: string
{
    case Service = 'service';
    case Class = 'class';
    case Discipleship = 'discipleship';
    case SpecialEvent = 'special_event';
    case Congress = 'congress';

    public function label(): string
    {
        return match($this) {
            self::Service => 'Culto',
            self::Class => 'Clase',
            self::Discipleship => 'Discipulado',
            self::SpecialEvent => 'Evento Especial',
            self::Congress => 'Congreso',
        };
    }
}
