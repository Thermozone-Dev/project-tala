<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'Super Admin',
            'Secretariat',
            'Chairman',
            'Vice Chairman',
            'Trustee',
            'Corporate Officer',
            'Lead Resource Person',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }

        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ]);

        $superAdmin->syncPermissions(Permission::all());

        $secretariat = Role::firstOrCreate([
            'name' => 'Secretariat',
            'guard_name' => 'web',
        ]);

        $secretariat->syncPermissions(
            Permission::where('name', 'not like', '%role%')->get()
        );
    }
}
