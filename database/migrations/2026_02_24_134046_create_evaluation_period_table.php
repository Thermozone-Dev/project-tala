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

        Schema::create('evaluation_period_status', function (Blueprint $table) {
            $table->id();
            $table->string('name',50);
            $table->softDeletes();
        });

        DB::table('evaluation_period_status')->insert([
            [
                'name' => 'Active',
            ],
            [
                'name' => 'Paused',
            ],
            [
                'name' => 'Completed',
            ],
            [
                'name' => 'Cancelled',
            ],
        ]);

        Schema::create('evaluation_period', function (Blueprint $table) {
            $table->id();
            $table->date('date_from');
            $table->date('date_to');
            $table->unsignedBigInteger('status_id')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->softDeletes();
            $table->foreign(['status_id'], 'evaluation_period_ibfk_1')->references(['id'])->on('evaluation_period_status')->onUpdate('cascade')->onDelete('cascade')->default(1);
            $table->foreign(['created_by'], 'evaluation_period_ibfk_2')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });


        Schema::create('trustee_has_evaluation', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evaluation_id');
            $table->unsignedBigInteger('ef_id');
            $table->unsignedBigInteger('committee_id');
            $table->unsignedBigInteger('member_id')->nullable();
            $table->unsignedBigInteger('evaluator_id');
            $table->foreign(['evaluation_id'], 'trustee_has_evaluation_ibfk_1')->references(['id'])->on('evaluation_period')->onUpdate('cascade')->onDelete('cascade')->default(1);
            $table->foreign(['ef_id'], 'trustee_has_evaluation_ibfk_2')->references(['id'])->on('evaluation_forms')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['committee_id'], 'trustee_has_evaluation_ibfk_3')->references(['id'])->on('committees')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['member_id'], 'trustee_has_evaluation_ibfk_4')->references(['id'])->on('users');
            $table->foreign(['evaluator_id'], 'trustee_has_evaluation_ibfk_5')->references(['id'])->on('users');
            $table->softDeletes();

        });


        Schema::table('rating_scales', function (Blueprint $table) {
           $table->string('name',20)->nullable()->after('type');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rating_scales', function (Blueprint $table) {
            $table->dropColumn('name');
         });
        Schema::dropIfExists('trustee_has_evaluation');
        Schema::dropIfExists('evaluation_period');
        Schema::dropIfExists('evaluation_period_status');
    }
};
