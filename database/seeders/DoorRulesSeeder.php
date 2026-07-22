<?php

namespace Database\Seeders;

use App\Models\Door;
use App\Models\DoorRule;
use App\Support\Enums\DoorCode;
use App\Support\Enums\DoorReferralPriority;
use Illuminate\Database\Seeder;

class DoorRulesSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            DoorCode::IntercesionProfetica->value => [
                [
                    'name' => 'Cobertura de nuevos integrantes de célula',
                    'description' => 'Cuando se añade un nuevo miembro a una célula, debe ser cubierto en oración por el equipo de intercesión durante al menos una semana.',
                    'event_types' => ['cell_member.added'],
                    'priority_hint' => DoorReferralPriority::Low,
                ],
            ],
            DoorCode::Bienvenida->value => [
                [
                    'name' => 'Bienvenida a visitantes nuevos',
                    'description' => 'Cualquier visitante registrado por primera vez debe recibir un contacto personal de bienvenida en los primeros 3 días.',
                    'event_types' => ['person.registered'],
                    'priority_hint' => DoorReferralPriority::Normal,
                ],
                [
                    'name' => 'Retorno tras ausencia prolongada',
                    'description' => 'Si una persona regresa después de 6 o más meses ausente, debe ser recibida con un seguimiento personalizado.',
                    'event_types' => ['person.returned_after_absence'],
                    'priority_hint' => DoorReferralPriority::Normal,
                ],
            ],
            DoorCode::AtencionPastoral->value => [
                [
                    'name' => 'Crisis emocional o espiritual',
                    'description' => 'Cuando un miembro reporta una crisis emocional, espiritual o familiar grave, debe recibir atención pastoral en menos de 48 horas.',
                    'event_types' => ['person.crisis_reported', 'person.health_status_reported'],
                    'priority_hint' => DoorReferralPriority::High,
                ],
            ],
            DoorCode::Retiros->value => [
                [
                    'name' => 'Candidatos para próximo retiro',
                    'description' => 'Las personas que avanzan a etapa de membresía Aprobación o superior son candidatas a participar en el próximo retiro.',
                    'event_types' => ['membership_stage.advanced'],
                    'priority_hint' => DoorReferralPriority::Low,
                ],
            ],
            DoorCode::Discipulado->value => [
                [
                    'name' => 'Asignación tras Clase',
                    'description' => 'Cuando un miembro completa la etapa Clase de membresía, debe ser asignado a un discipulado inicial.',
                    'event_types' => ['membership_stage.advanced'],
                    'priority_hint' => DoorReferralPriority::Normal,
                ],
            ],
            DoorCode::Visitacion->value => [
                [
                    'name' => 'Inasistencia prolongada',
                    'description' => 'Miembros activos que falten a 3 o más reuniones consecutivas deben recibir una visita en su casa. Si la persona vive sola o tiene más de 65 años, prioridad alta.',
                    'event_types' => ['attendance.missed_3'],
                    'priority_hint' => DoorReferralPriority::High,
                ],
                [
                    'name' => 'Enfermedad u hospitalización',
                    'description' => 'Si un miembro está enfermo u hospitalizado, debe ser visitado dentro de las primeras 24 horas.',
                    'event_types' => ['person.health_status_reported'],
                    'priority_hint' => DoorReferralPriority::Urgent,
                ],
            ],
            DoorCode::ComunicacionTeatro->value => [
                [
                    'name' => 'Promoción de eventos',
                    'description' => 'Cuando se registra un evento o congreso nuevo, el equipo de comunicación debe coordinar la promoción al menos 2 semanas antes de la fecha.',
                    'event_types' => ['event.created', 'congress.created'],
                    'priority_hint' => DoorReferralPriority::Normal,
                ],
            ],
            DoorCode::AdminFinanzasConsolidacion->value => [
                [
                    'name' => 'Seguimiento de consolidación financiera',
                    'description' => 'Miembros activos sin registro de diezmo en los últimos 60 días deben recibir seguimiento de consolidación.',
                    'event_types' => ['finance.tithe_missing_60d'],
                    'priority_hint' => DoorReferralPriority::Low,
                ],
            ],
            DoorCode::EventosCongresos->value => [
                [
                    'name' => 'Conformación de equipo de congreso',
                    'description' => 'Cuando se crea un congreso, debe formarse el equipo organizativo de inmediato.',
                    'event_types' => ['congress.created'],
                    'priority_hint' => DoorReferralPriority::High,
                ],
            ],
        ];

        foreach ($rules as $code => $doorRules) {
            $door = Door::where('code', $code)->first();
            if (! $door) {
                continue;
            }

            foreach ($doorRules as $rule) {
                DoorRule::updateOrCreate(
                    ['door_id' => $door->id, 'name' => $rule['name']],
                    $rule + ['door_id' => $door->id, 'is_enabled' => true],
                );
            }
        }
    }
}
