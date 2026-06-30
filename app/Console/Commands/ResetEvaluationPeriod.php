<?php

namespace App\Console\Commands;

use App\Models\EvaluationPeriod;
use App\Models\TrusteeHasEvaluation;
use App\Models\QuestionaireAnswer;
use App\Models\OtherCommentAnswer;
use App\Models\AttendanceAnswer;
use App\Models\Report;
use App\Models\Meeting;
use App\Models\MeetingAttendee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetEvaluationPeriod extends Command
{


    /**
     * Run the database seeds.
     *
     * Use the artisan command instead:
     * - php artisan evaluation:reset                (reset all evaluation periods)
     * - php artisan evaluation:reset {evaluation_id}  (reset specific evaluation period)
    */


    protected $signature = 'evaluation:reset {evaluation_id?}';
    protected $description = 'Reset evaluation period data with optional specific period ID';

    public function handle(): int
    {
        $evaluationId = $this->argument('evaluation_id');

        if ($evaluationId) {
            return $this->resetSpecificPeriod($evaluationId);
        }

        return $this->resetAllPeriods();
    }

    private function resetSpecificPeriod(int $evaluationId): int
    {
        $period = EvaluationPeriod::find($evaluationId);

        if (!$period) {
            $this->error("Evaluation period with ID {$evaluationId} not found.");
            return 1;
        }

        $this->info("Date Range: {$period->date_from} to {$period->date_to}");

        if (!$this->confirm('Are you sure you want to delete this evaluation period and all related data?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        try {
            DB::transaction(function () use ($evaluationId) {
                $trusteeEvals = TrusteeHasEvaluation::where('evaluation_id', $evaluationId)->pluck('id');
                $meetings = Meeting::where('evaluation_period_id', $evaluationId)->pluck('id');

                $this->line('Deleting related data...');

                QuestionaireAnswer::whereIn('trustee_evaluation_id', $trusteeEvals)->delete();
                $this->line('✓ Deleted questionnaire answers');

                OtherCommentAnswer::whereIn('trustee_evaluation_id', $trusteeEvals)->delete();
                $this->line('✓ Deleted other comments');

                AttendanceAnswer::where('evaluation_period_id', $evaluationId)->delete();
                $this->line('✓ Deleted attendance answers');

                MeetingAttendee::whereIn('meeting_id', $meetings)->delete();
                $this->line('✓ Deleted meeting attendees');

                Meeting::where('evaluation_period_id', $evaluationId)->delete();
                $this->line('✓ Deleted meetings');

                TrusteeHasEvaluation::where('evaluation_id', $evaluationId)->forceDelete();
                $this->line('✓ Deleted trustee evaluations');

                Report::where('evaluation_period_id', $evaluationId)->forceDelete();
                $this->line('✓ Deleted connected reports');

                EvaluationPeriod::find($evaluationId)->forceDelete();
                $this->line('✓ Deleted evaluation period');
            });

            $this->info('✓ Evaluation period reset successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Error during deletion: ' . $e->getMessage());
            return 1;
        }
    }

    private function resetAllPeriods(): int
    {
        $count = EvaluationPeriod::count();
        $this->info("Total evaluation periods: {$count}");

        if (!$this->confirm('Are you sure you want to delete ALL evaluation periods and all related data? This cannot be undone!')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        if (!$this->confirm('⚠️  FINAL CONFIRMATION: Delete ALL evaluation data?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        try {
            DB::transaction(function () {
                $this->line('Deleting all evaluation data...');

                QuestionaireAnswer::query()->delete();
                $this->line('✓ Deleted all questionnaire answers');

                OtherCommentAnswer::query()->delete();
                $this->line('✓ Deleted all other comments');

                AttendanceAnswer::query()->delete();
                $this->line('✓ Deleted all attendance answers');

                MeetingAttendee::query()->delete();
                $this->line('✓ Deleted all meeting attendees');

                Meeting::query()->delete();
                $this->line('✓ Deleted all meetings');

                TrusteeHasEvaluation::query()->forceDelete();
                $this->line('✓ Deleted all trustee evaluations');

                Report::query()->forceDelete();
                $this->line('✓ Deleted all reports');

                EvaluationPeriod::query()->forceDelete();
                $this->line('✓ Deleted all evaluation periods');
            });

            $this->info('✓ All evaluation periods reset successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Error during deletion: ' . $e->getMessage());
            return 1;
        }
    }
}
