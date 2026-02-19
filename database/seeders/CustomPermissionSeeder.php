<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CustomPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionName = 'FullAccess:Committee';

        $permission = Permission::firstOrCreate([
            'name' => $permissionName,
            'guard_name' => 'web'
        ]);

        $roles = Role::whereIn('name', ['Super Admin','Secretariat'])->get();

        foreach ($roles as $role) {
            $role->givePermissionTo($permission);
        }
    }
}
