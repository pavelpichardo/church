<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // People
            'people.view', 'people.create', 'people.update', 'people.delete',
            // Membership
            'membership.view', 'membership.advance', 'membership.approve',
            // Discipleship
            'discipleships.view', 'discipleships.create', 'discipleships.update', 'discipleships.delete',
            'discipleships.assign', 'discipleships.complete',
            // Library
            'library.view', 'library.create', 'library.update', 'library.delete',
            'library.loan', 'library.return',
            // Attendance
            'attendance.view', 'attendance.record',
            // Events
            'events.view', 'events.create', 'events.update', 'events.delete',
            // Sacraments
            'sacraments.view', 'sacraments.create',
            // Communication
            'communication.view', 'communication.send',
            // Admin
            'users.view', 'users.create', 'users.update', 'users.delete',
            'roles.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Admin: all permissions
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        // Pastor: all except user/role management
        $pastor = Role::firstOrCreate(['name' => 'pastor']);
        $pastor->syncPermissions(Permission::whereNotIn('name', ['users.delete', 'roles.manage'])->get());

        // Leader: people, attendance, discipleship (view/assign), events (view), library (view/loan)
        $leader = Role::firstOrCreate(['name' => 'leader']);
        $leader->syncPermissions([
            'people.view', 'people.create', 'people.update',
            'membership.view',
            'discipleships.view', 'discipleships.assign',
            'library.view', 'library.loan', 'library.return',
            'attendance.view', 'attendance.record',
            'events.view',
        ]);

        // Secretary: people CRUD, attendance, events, library (view/loan)
        $secretary = Role::firstOrCreate(['name' => 'secretary']);
        $secretary->syncPermissions([
            'people.view', 'people.create', 'people.update',
            'membership.view', 'membership.advance',
            'discipleships.view',
            'library.view', 'library.loan', 'library.return',
            'attendance.view', 'attendance.record',
            'events.view', 'events.create', 'events.update',
        ]);
    }
}
