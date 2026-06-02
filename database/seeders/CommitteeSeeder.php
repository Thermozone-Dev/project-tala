<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CommitteeSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        $committees = [
            [
                'name' => 'Governance',
                'description' => 'Ensures proper governance practices and compliance with bylaws.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Risk Oversight',
                'description' => 'Monitors and manages organizational risks and mitigation strategies.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'HR and Compensation',
                'description' => 'Manages human resources policies, compensation, and performance evaluation.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'IT Steering',
                'description' => 'Guides IT strategy, digital initiatives, and technology investments.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Credit and Collection',
                'description' => 'Oversees credit policies, lending decisions, and collection processes.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Audit and Compliance',
                'description' => 'Ensures proper auditing, regulatory compliance, and internal controls.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Membership and Amendment',
                'description' => 'Handles membership approvals and amendments to organizational bylaws.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('committees')->insert($committees);
    }
}
