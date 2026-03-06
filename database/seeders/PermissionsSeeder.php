<?php

namespace Database\Seeders;

use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $resources =  collect(FilamentShield::getResources())->toArray();

        collect($resources)
            ->values()
            ->each(function (array $entity): void {
                collect(FilamentShield::getResourcePermissions($entity['resourceFqcn']))
                    ->map(Utils::createPermission(...))
                    ->toArray();
            });
    }
}
