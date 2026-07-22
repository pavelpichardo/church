<?php

namespace Database\Seeders;

use App\Models\Door;
use App\Support\Enums\DoorCode;
use Illuminate\Database\Seeder;

class DoorSeeder extends Seeder
{
    public function run(): void
    {
        $doors = [
            [
                'code' => DoorCode::IntercesionProfetica->value,
                'name' => 'Intercesión Profética',
                'order' => 1,
                'description' => 'Oración intercesora y cobertura espiritual sobre la iglesia, sus miembros, líderes y procesos.',
                'color' => '#7c3aed',
                'icon' => 'hands-praying',
            ],
            [
                'code' => DoorCode::Bienvenida->value,
                'name' => 'Bienvenida',
                'order' => 2,
                'description' => 'Primer contacto con visitantes y personas que regresan después de una ausencia prolongada.',
                'color' => '#06b6d4',
                'icon' => 'hand-wave',
            ],
            [
                'code' => DoorCode::AtencionPastoral->value,
                'name' => 'Atención Pastoral',
                'order' => 3,
                'description' => 'Consejería pastoral, atención de crisis emocionales, espirituales y familiares.',
                'color' => '#ec4899',
                'icon' => 'heart',
            ],
            [
                'code' => DoorCode::Retiros->value,
                'name' => 'Retiros',
                'order' => 4,
                'description' => 'Organización y logística de retiros espirituales y encuentros de crecimiento.',
                'color' => '#10b981',
                'icon' => 'tent',
            ],
            [
                'code' => DoorCode::Discipulado->value,
                'name' => 'Discipulado',
                'order' => 5,
                'description' => 'Formación discipular, asignación de discipulados y seguimiento de progreso.',
                'color' => '#3b82f6',
                'icon' => 'book-open',
            ],
            [
                'code' => DoorCode::Visitacion->value,
                'name' => 'Visitación',
                'order' => 6,
                'description' => 'Visitas a enfermos, hospitalizados, ausentes prolongados y miembros en seguimiento.',
                'color' => '#f59e0b',
                'icon' => 'home',
            ],
            [
                'code' => DoorCode::ComunicacionTeatro->value,
                'name' => 'Comunicación y Teatro',
                'order' => 7,
                'description' => 'Medios, redes sociales, ministerio creativo y producción artística.',
                'color' => '#ef4444',
                'icon' => 'megaphone',
            ],
            [
                'code' => DoorCode::AdminFinanzasConsolidacion->value,
                'name' => 'Admin. Finanzas y Consolidación',
                'order' => 8,
                'description' => 'Administración financiera, seguimiento de diezmos y procesos de consolidación.',
                'color' => '#6b7280',
                'icon' => 'chart-bar',
            ],
            [
                'code' => DoorCode::EventosCongresos->value,
                'name' => 'Eventos / Congresos',
                'order' => 9,
                'description' => 'Planificación y ejecución de eventos especiales y congresos.',
                'color' => '#f97316',
                'icon' => 'calendar-days',
            ],
        ];

        foreach ($doors as $door) {
            Door::updateOrCreate(['code' => $door['code']], $door + ['is_active' => true]);
        }
    }
}
