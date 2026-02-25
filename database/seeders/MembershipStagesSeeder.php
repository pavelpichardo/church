<?php

namespace Database\Seeders;

use App\Models\MembershipStage;
use Illuminate\Database\Seeder;

class MembershipStagesSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            ['name' => 'Visitante',       'order' => 1, 'is_active' => true],
            ['name' => 'Clase',           'order' => 2, 'is_active' => true],
            ['name' => 'Firma',           'order' => 3, 'is_active' => true],
            ['name' => 'Aprobación',      'order' => 4, 'is_active' => true],
            ['name' => 'Miembro',         'order' => 5, 'is_active' => true],
            ['name' => 'Miembro Activo',  'order' => 6, 'is_active' => true],
        ];

        foreach ($stages as $stage) {
            MembershipStage::firstOrCreate(['name' => $stage['name']], $stage);
        }
    }
}
