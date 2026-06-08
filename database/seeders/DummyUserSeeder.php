<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DummyUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = Role::all();

        foreach ($roles as $role) {

            if ($role->name === 'super_admin') continue;

            $slug = str($role->name)->slug('_');

            $user = User::firstOrCreate(
                ['email' => "{$slug}@dummy.com"],
                [
                    'first_name' => 'Dummy',
                    'last_name' => $role->name,
                    'name' => 'Dummy '.$role->name,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$role->name]);
        }
    }
}
