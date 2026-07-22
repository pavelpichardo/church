<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            MembershipStagesSeeder::class,
            DoorSeeder::class,
            DoorRulesSeeder::class,
            AdminUserSeeder::class,
            TestDataSeeder::class,
        ]);
    }
}
