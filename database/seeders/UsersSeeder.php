<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $super_admin = Role::firstOrCreate(["name" => "Super Admin", "guard_name" => "web"]);
        $secretariat = Role::firstOrCreate(["name" => "Secretariat", "guard_name" => "web"]);

        $super_admin_user = User::updateOrCreate(
            ["email" => "admin@admin.com"],
            [
                "name" => "Super Admin",
                "password" => bcrypt("password123"),
                "first_name" => "Super",
                "last_name" => "Admin",
            ]
        );

        $super_admin_user->assignRole($super_admin);

        $secretariat_user = User::updateOrCreate(
            ["email" => "secretariat@admin.com"],
            [
                "name" => "Secretariat",
                "password" => bcrypt("password123"),
                "first_name" => "Secretariat",
                "last_name" => "Admin",
            ]
        );

        $secretariat_user->assignRole($secretariat);

    }
}
