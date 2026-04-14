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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_period_id')
                ->constrained('evaluation_period')
                ->noActionOnDelete();
            $table->foreignId('report_type_id')
                ->constrained('report_types')
                ->noActionOnDelete();
            $table->foreignId('generated_by')
                ->constrained('users')
                ->noActionOnDelete();
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
