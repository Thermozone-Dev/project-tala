<?php

namespace Database\Seeders;

use App\Models\SectionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SectionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
          SectionType::insert([
            ['name' => 'Assesment'],
            ['name' => 'Attendance'],
            ['name' => 'Other Comments'],
       ]);

    }
}
