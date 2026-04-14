<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('trustee_evaluation_statuses')->where('name', 'Locked')->update(['name' => 'Submitted']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('trustee_evaluation_statuses')->where('name', 'Submitted')->update(['name' => 'Locked']);
    }
};
