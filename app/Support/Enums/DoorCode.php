<?php

namespace App\Support\Enums;

enum DoorCode: string
{
    case IntercesionProfetica = 'intercesion_profetica';
    case Bienvenida = 'bienvenida';
    case AtencionPastoral = 'atencion_pastoral';
    case Retiros = 'retiros';
    case Discipulado = 'discipulado';
    case Visitacion = 'visitacion';
    case ComunicacionTeatro = 'comunicacion_teatro';
    case AdminFinanzasConsolidacion = 'admin_finanzas_consolidacion';
    case EventosCongresos = 'eventos_congresos';

    public function label(): string
    {
        return match ($this) {
            self::IntercesionProfetica => 'Intercesión Profética',
            self::Bienvenida => 'Bienvenida',
            self::AtencionPastoral => 'Atención Pastoral',
            self::Retiros => 'Retiros',
            self::Discipulado => 'Discipulado',
            self::Visitacion => 'Visitación',
            self::ComunicacionTeatro => 'Comunicación y Teatro',
            self::AdminFinanzasConsolidacion => 'Admin. Finanzas y Consolidación',
            self::EventosCongresos => 'Eventos / Congresos',
        };
    }

    public function order(): int
    {
        return match ($this) {
            self::IntercesionProfetica => 1,
            self::Bienvenida => 2,
            self::AtencionPastoral => 3,
            self::Retiros => 4,
            self::Discipulado => 5,
            self::Visitacion => 6,
            self::ComunicacionTeatro => 7,
            self::AdminFinanzasConsolidacion => 8,
            self::EventosCongresos => 9,
        };
    }
}
