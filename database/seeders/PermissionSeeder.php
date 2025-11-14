<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'users.read',
            'users.update',
            'users.delete',
            'users.invite',
            'analytics.read',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Fetch all permissions
        $allPermissions = Permission::pluck('id')->toArray();

        // Assign all permissions to "owner"
        $owner = Role::firstOrCreate(['name' => 'owner']);
        $owner->permissions()->sync($allPermissions);

        // Assign limited permissions to other roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->permissions()->sync($allPermissions);

        $member = Role::firstOrCreate(['name' => 'member']);
        $member->permissions()->sync([
            Permission::where('name', 'users.read')->first()->id,
        ]);

        $auditor = Role::firstOrCreate(['name' => 'auditor']);
        $auditor->permissions()->sync([
            Permission::where('name', 'analytics.read')->first()->id,
        ]);
    }
}
