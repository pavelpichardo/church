<?php

namespace App\Domain\Doors;

/**
 * Single source of truth for the routing event types that the doors engine
 * can react to. Used to populate suggestions in the rule editor so admins
 * don't have to memorize event slugs.
 *
 * Keep this in sync with the events implementing
 * App\Events\Contracts\RoutingTriggerEvent.
 */
class RoutingEventCatalog
{
    /**
     * Slug => human-readable label (Spanish).
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [
            'person.registered' => 'Persona registrada (nuevo visitante o miembro)',
            'cell_member.added' => 'Persona añadida a una célula',
            'membership_stage.advanced' => 'Avance de etapa de membresía',
            'congress.created' => 'Congreso creado',
            'person.health_status_reported' => 'Reporte de salud / enfermedad',
            'attendance.missed_3' => '3 o más inasistencias consecutivas',
            'birthday.upcoming_7d' => 'Cumpleaños próximo (dentro de 7 días)',
            'person.returned_after_absence' => 'Regreso tras ausencia prolongada (2+ meses)',
            'person.note_added' => 'Nota agregada al perfil de la persona',
            'person.contact_failed' => 'No se pudo contactar a la persona',
        ];
    }

    /**
     * @return list<string>
     */
    public static function slugs(): array
    {
        return array_keys(self::all());
    }

    public static function label(string $slug): ?string
    {
        return self::all()[$slug] ?? null;
    }
}
