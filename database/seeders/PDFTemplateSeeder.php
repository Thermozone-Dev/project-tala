<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PDFTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $isSqlServer = DB::getDriverName() === "sqlsrv";

        if ($isSqlServer) DB::unprepared("SET IDENTITY_INSERT pdf_templates ON");

        DB::table("pdf_templates")->insert([
            [
                "id" => 1,
                "name" => "Self Assessment",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => 2,
                "name" => "C1-C7 Forms",
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ]);

        if ($isSqlServer) DB::unprepared("SET IDENTITY_INSERT pdf_templates OFF");

        if ($isSqlServer) DB::unprepared("SET IDENTITY_INSERT section_types ON");

        DB::table("section_types")->insert([
            [
                "id" => 1,
                "name" => "Assesment",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => 2,
                "name" => "Attendance",
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "id" => 3,
                "name" => "Other Comments",
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ]);

        if ($isSqlServer) DB::unprepared("SET IDENTITY_INSERT section_types OFF");
    }
}
