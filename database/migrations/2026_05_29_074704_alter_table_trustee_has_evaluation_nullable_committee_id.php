<?php

use App\Models\TrusteeEvaluationStatus;
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
        Schema::table('trustee_has_evaluation', function (Blueprint $table) {
            $table->unsignedBigInteger('committee_id')->nullable()->change();
        });

        $in_prog = TrusteeEvaluationStatus::where('name','Draft')->first();
        if($in_prog){
            $in_prog->name = 'In Progress';
            $in_prog->update();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trustee_has_evaluation', function (Blueprint $table) {
            $table->unsignedBigInteger('committee_id')->nullable(false)->change();
        });

        // Revert status name change
        $draft = TrusteeEvaluationStatus::where('name', 'In Progress')->first();
        if ($draft) {
            $draft->name = 'Draft';
            $draft->update();
        }
    }
};
