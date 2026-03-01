<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\DiscipleshipAssignment;
use App\Models\Discipleship;
use App\Models\Event;
use App\Models\MembershipStage;
use App\Models\MembershipStageHistory;
use App\Models\Person;
use App\Models\PersonMembership;
use App\Models\StudyMaterial;
use App\Models\MaterialLoan;
use App\Models\User;
use App\Support\Enums\EventType;
use App\Support\Enums\PersonStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Additional users with different roles
        $pastor = User::firstOrCreate(
            ['email' => 'pastor@church.local'],
            ['name' => 'Pastor Roberto García', 'password' => Hash::make('password'), 'is_active' => true]
        );
        $pastor->assignRole('pastor');

        $leader = User::firstOrCreate(
            ['email' => 'lider@church.local'],
            ['name' => 'Lider María López', 'password' => Hash::make('password'), 'is_active' => true]
        );
        $leader->assignRole('leader');

        $secretary = User::firstOrCreate(
            ['email' => 'secretaria@church.local'],
            ['name' => 'Secretaria Ana Martínez', 'password' => Hash::make('password'), 'is_active' => true]
        );
        $secretary->assignRole('secretary');

        $admin = User::where('email', 'admin@church.local')->first();

        // People
        $people = [
            ['first_name' => 'Juan',     'last_name' => 'Pérez',      'email' => 'juan@example.com',    'phone' => '614-555-0101', 'status' => PersonStatus::ActiveMember,     'birth_date' => '1985-03-15', 'first_visit_date' => '2020-01-05'],
            ['first_name' => 'María',    'last_name' => 'González',   'email' => 'maria@example.com',   'phone' => '614-555-0102', 'status' => PersonStatus::Member,           'birth_date' => '1990-07-22', 'first_visit_date' => '2021-03-10'],
            ['first_name' => 'Carlos',   'last_name' => 'Rodríguez',  'email' => 'carlos@example.com',  'phone' => '614-555-0103', 'status' => PersonStatus::MembershipProcess, 'birth_date' => '1988-11-08', 'first_visit_date' => '2023-06-01'],
            ['first_name' => 'Ana',      'last_name' => 'Hernández',  'email' => 'ana@example.com',     'phone' => '614-555-0104', 'status' => PersonStatus::Visitor,          'birth_date' => '1995-04-30', 'first_visit_date' => '2025-11-15'],
            ['first_name' => 'Luis',     'last_name' => 'Martínez',   'email' => 'luis@example.com',    'phone' => '614-555-0105', 'status' => PersonStatus::ActiveMember,     'birth_date' => '1978-09-12', 'first_visit_date' => '2018-08-20'],
            ['first_name' => 'Carmen',   'last_name' => 'López',      'email' => 'carmen@example.com',  'phone' => '614-555-0106', 'status' => PersonStatus::Member,           'birth_date' => '1992-01-25', 'first_visit_date' => '2022-02-14'],
            ['first_name' => 'Pedro',    'last_name' => 'Ramírez',    'email' => 'pedro@example.com',   'phone' => '614-555-0107', 'status' => PersonStatus::MembershipProcess, 'birth_date' => '1987-06-18', 'first_visit_date' => '2024-09-03'],
            ['first_name' => 'Sofía',    'last_name' => 'Torres',     'email' => 'sofia@example.com',   'phone' => '614-555-0108', 'status' => PersonStatus::Visitor,          'birth_date' => '2000-12-05', 'first_visit_date' => '2025-12-01'],
            ['first_name' => 'Miguel',   'last_name' => 'Flores',     'email' => 'miguel@example.com',  'phone' => '614-555-0109', 'status' => PersonStatus::ActiveMember,     'birth_date' => '1983-08-27', 'first_visit_date' => '2019-05-11'],
            ['first_name' => 'Isabel',   'last_name' => 'Díaz',       'email' => 'isabel@example.com',  'phone' => '614-555-0110', 'status' => PersonStatus::Inactive,         'birth_date' => '1975-02-14', 'first_visit_date' => '2017-04-08'],
        ];

        $personModels = [];
        foreach ($people as $personData) {
            $personModels[] = Person::firstOrCreate(
                ['email' => $personData['email']],
                array_merge($personData, ['created_by' => $admin?->id])
            );
        }

        // Membership stages
        $visitanteStage    = MembershipStage::where('name', 'Visitante')->first();
        $claseStage        = MembershipStage::where('name', 'Clase')->first();
        $firmaStage        = MembershipStage::where('name', 'Firma')->first();
        $aprobacionStage   = MembershipStage::where('name', 'Aprobación')->first();
        $miembroStage      = MembershipStage::where('name', 'Miembro')->first();
        $miembroActivoStage = MembershipStage::where('name', 'Miembro Activo')->first();

        // Assign membership records for non-visitor people
        $membershipMap = [
            0 => $miembroActivoStage, // Juan - active member
            1 => $miembroStage,       // María - member
            2 => $claseStage,         // Carlos - in process
            4 => $miembroActivoStage, // Luis - active member
            5 => $miembroStage,       // Carmen - member
            6 => $firmaStage,         // Pedro - in process (firma stage)
            8 => $miembroActivoStage, // Miguel - active member
        ];

        foreach ($membershipMap as $idx => $stage) {
            if (!$stage) continue;
            $person = $personModels[$idx];
            PersonMembership::firstOrCreate(
                ['person_id' => $person->id],
                ['current_stage_id' => $stage->id]
            );
            MembershipStageHistory::firstOrCreate(
                ['person_id' => $person->id, 'to_stage_id' => $stage->id],
                [
                    'from_stage_id' => $visitanteStage?->id,
                    'changed_by'    => $admin?->id,
                    'changed_at'    => now()->subMonths(rand(1, 24)),
                    'note'          => 'Registro inicial de prueba',
                ]
            );
        }

        // Events
        $events = [
            ['title' => 'Culto Dominical',              'event_type' => EventType::Service,    'starts_at' => now()->startOfWeek()->addDays(6)->setTime(10, 0), 'ends_at' => now()->startOfWeek()->addDays(6)->setTime(12, 0), 'location' => 'Santuario Principal'],
            ['title' => 'Culto de Oración',             'event_type' => EventType::Service,    'starts_at' => now()->startOfWeek()->addDays(2)->setTime(19, 0), 'ends_at' => now()->startOfWeek()->addDays(2)->setTime(20, 30), 'location' => 'Santuario Principal'],
            ['title' => 'Clase de Nuevos Convertidos',  'event_type' => EventType::ClassEvent, 'starts_at' => now()->subWeeks(2)->setTime(9, 0),  'ends_at' => now()->subWeeks(2)->setTime(11, 0),  'location' => 'Salón 101'],
            ['title' => 'Discipulado Nivel Inicial',    'event_type' => EventType::Discipleship,'starts_at' => now()->subWeek()->setTime(18, 0),  'ends_at' => now()->subWeek()->setTime(20, 0),   'location' => 'Salón 202'],
            ['title' => 'Congreso de Jóvenes 2026',     'event_type' => EventType::Congress,   'starts_at' => now()->addMonth()->setTime(8, 0),  'ends_at' => now()->addMonth()->addDays(2)->setTime(22, 0), 'location' => 'Centro de Convenciones'],
            ['title' => 'Noche de Adoración Especial',  'event_type' => EventType::SpecialEvent,'starts_at' => now()->addWeeks(2)->setTime(19, 0),'ends_at' => now()->addWeeks(2)->setTime(21, 0), 'location' => 'Santuario Principal'],
        ];

        $eventModels = [];
        foreach ($events as $eventData) {
            $eventModels[] = Event::firstOrCreate(
                ['title' => $eventData['title'], 'starts_at' => $eventData['starts_at']],
                array_merge($eventData, [
                    'event_type' => $eventData['event_type']->value,
                    'created_by' => $admin?->id,
                ])
            );
        }

        // Attendance records (for past events only)
        $pastEventModels = array_slice($eventModels, 2, 2); // Class and Discipleship events
        $attendees = array_slice($personModels, 0, 6);

        foreach ($pastEventModels as $event) {
            foreach ($attendees as $person) {
                AttendanceRecord::firstOrCreate(
                    ['event_id' => $event->id, 'person_id' => $person->id],
                    [
                        'checked_in_at'  => $event->starts_at,
                        'checkin_method' => 'manual',
                        'recorded_by'    => $leader->id,
                    ]
                );
            }
        }

        // Discipleships
        $discipleships = [
            ['name' => 'Fundamentos de la Fe',   'level' => 'initial',       'duration_weeks' => 8,  'leader_id' => $leader->id, 'description' => 'Bases de la vida cristiana'],
            ['name' => 'Vida de Oración',         'level' => 'intermediate',  'duration_weeks' => 6,  'leader_id' => $pastor->id, 'description' => 'Profundizando en la oración'],
            ['name' => 'Liderazgo Cristiano',     'level' => 'advanced',      'duration_weeks' => 12, 'leader_id' => $pastor->id, 'description' => 'Formación de líderes'],
        ];

        $discipleshipModels = [];
        foreach ($discipleships as $data) {
            $discipleshipModels[] = Discipleship::firstOrCreate(['name' => $data['name']], $data);
        }

        // Discipleship assignments
        $assignmentData = [
            [$personModels[0], $discipleshipModels[0], 'completed'],
            [$personModels[1], $discipleshipModels[0], 'completed'],
            [$personModels[2], $discipleshipModels[1], 'in_progress'],
            [$personModels[4], $discipleshipModels[2], 'completed'],
            [$personModels[6], $discipleshipModels[1], 'in_progress'],
        ];

        foreach ($assignmentData as [$person, $discipleship, $status]) {
            DiscipleshipAssignment::firstOrCreate(
                ['person_id' => $person->id, 'discipleship_id' => $discipleship->id],
                [
                    'assigned_by' => $leader->id,
                    'start_date'  => now()->subMonths(rand(2, 10)),
                    'end_date'    => $status === 'completed' ? now()->subMonth() : null,
                    'status'      => $status,
                ]
            );
        }

        // Study materials
        $materials = [
            ['title' => 'La Biblia de Estudio NVI',       'author' => 'Varios',         'material_type' => 'book',   'total_quantity' => 5, 'available_quantity' => 3, 'description' => 'Biblia con notas de estudio'],
            ['title' => 'Propósito de Vida',               'author' => 'Rick Warren',    'material_type' => 'book',   'total_quantity' => 8, 'available_quantity' => 6, 'description' => 'Los 40 días de propósito'],
            ['title' => 'Manual Discipulado Inicial',      'author' => 'Iglesia Nazareno','material_type' => 'manual', 'total_quantity' => 10,'available_quantity' => 8, 'description' => 'Material oficial nivel inicial'],
            ['title' => 'El Poder de la Oración',         'author' => 'E.M. Bounds',    'material_type' => 'book',   'total_quantity' => 4, 'available_quantity' => 4, 'description' => 'Clásico sobre la vida de oración'],
            ['title' => 'Teología Wesleyana (PDF)',        'author' => 'H. Orton Wiley', 'material_type' => 'pdf',    'total_quantity' => 99,'available_quantity' => 99,'description' => 'Copia digital disponible'],
        ];

        $materialModels = [];
        foreach ($materials as $data) {
            $materialModels[] = StudyMaterial::firstOrCreate(['title' => $data['title']], $data);
        }

        // Material loans
        MaterialLoan::firstOrCreate(
            ['study_material_id' => $materialModels[0]->id, 'person_id' => $personModels[2]->id],
            [
                'assigned_by' => $leader->id,
                'assigned_at' => now()->subDays(14),
                'due_at'      => now()->addDays(7),
                'status'      => 'borrowed',
            ]
        );

        MaterialLoan::firstOrCreate(
            ['study_material_id' => $materialModels[1]->id, 'person_id' => $personModels[5]->id],
            [
                'assigned_by' => $leader->id,
                'assigned_at' => now()->subDays(30),
                'due_at'      => now()->subDays(5),
                'status'      => 'overdue',
            ]
        );
    }
}
