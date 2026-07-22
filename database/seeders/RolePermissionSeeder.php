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
            // Cells
            'cells.view', 'cells.create', 'cells.update', 'cells.delete', 'cells.multiply',
            // Doors
            'doors.view', 'doors.manage',
            'door_members.manage',
            'door_activities.view', 'door_activities.manage',
            'referrals.view', 'referrals.create', 'referrals.assign', 'referrals.close', 'referrals.review_pending',
            'door_rules.view', 'door_rules.manage',
            'door_alerts.view', 'door_alerts.manage',
            'door_ai_inferences.view',
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
            'cells.view', 'cells.create', 'cells.update',
            'doors.view',
            'door_activities.view',
            'referrals.view', 'referrals.create', 'referrals.assign', 'referrals.close', 'referrals.review_pending',
            'door_alerts.view',
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
            'cells.view',
            'doors.view',
            'door_activities.view',
            'referrals.view', 'referrals.create',
            'door_alerts.view',
        ]);
    }
}
