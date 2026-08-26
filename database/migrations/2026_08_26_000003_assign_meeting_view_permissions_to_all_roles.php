<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'ViewAny:Meeting',
            'View:Meeting',
        ];

        // Get all permissions
        $permissionsToAssign = Permission::whereIn('name', $permissions)->get();

        // Assign to all roles
        $roles = Role::all();
        foreach ($roles as $role) {
            $role->givePermissionTo($permissionsToAssign);
        }
    }

    public function down(): void
    {
        $permissions = [
            'ViewAny:Meeting',
            'View:Meeting',
        ];

        // Remove from all roles
        $roles = Role::all();
        foreach ($roles as $role) {
            $role->revokePermissionTo($permissions);
        }
    }
};
