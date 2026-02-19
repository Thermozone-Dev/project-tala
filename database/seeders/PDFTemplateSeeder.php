<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PDFTemplateSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pdf_templates')->insert([
            [
                'id' => 1,
                'name' => 'Self Assessment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'C1-C7 Forms',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);


        DB::table('section_types')->insert([
            [
                'id' => 1,
                'name' => 'Assesment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Attendance',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Other Comments',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
