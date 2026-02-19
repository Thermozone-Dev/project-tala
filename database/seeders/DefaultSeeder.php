<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            PDFTemplateSeeder::class,
            RatingScalesSeeder::class,
            EvaluationC1Seeder::class,
            EvaluationC2Seeder::class,
            EvaluationC3Seeder::class,
            EvaluationC4Seeder::class,
            EvaluationC5Seeder::class,
            EvaluationC6Seeder::class,
            EvaluationC7Seeder::class,
            BOARDSelfAssesmentFormSeeder::class,
            BOTSelfAssesmentFormSeeder::class,

        ]);
    }
}
