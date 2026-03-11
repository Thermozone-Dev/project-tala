<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */


    public function up(): void
    {
        Schema::create('trustee_evaluation_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name',50);
        });

        DB::table('trustee_evaluation_statuses')->insert([
            ['name' => 'Draft'],
            ['name' => 'Locked'],
            ['name' => 'Pending'],
            ['name' => 'For Review'],
        ]);


        Schema::table('trustee_has_evaluation', function (Blueprint $table) {
            $table->unsignedBigInteger('trustee_evaluation_statuses_id')->default(3)->nullable()->after('evaluator_id');
            $table->foreign(['trustee_evaluation_statuses_id'], 'table_trustee_has_evaluation_ibfk_6')->references(['id'])->on('trustee_evaluation_statuses')->onUpdate('cascade')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('trustee_has_evaluation', function (Blueprint $table) {
            $table->dropForeign('table_trustee_has_evaluation_ibfk_6');
            $table->dropColumn('trustee_evaluation_statuses_id');
        });

        Schema::dropIfExists('trustee_evaluation_statuses');
    }
};
