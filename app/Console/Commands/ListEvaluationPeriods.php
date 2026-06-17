<?php

namespace App\Console\Commands;

use App\Models\EvaluationPeriod;
use App\Models\TrusteeHasEvaluation;
use Illuminate\Console\Command;

class ListEvaluationPeriods extends Command
{
    protected $signature = 'evaluation:list';
    protected $description = 'List all evaluation periods with their details';

    public function handle(): int
    {
        $periods = EvaluationPeriod::select([
            'id',
            'date_from',
            'date_to',
            'status_id',
        ])->get();

        if ($periods->isEmpty()) {
            $this->warn('No evaluation periods found.');
            return 0;
        }

        $headers = ['ID',  'Date From', 'Date To', 'Status', 'Evaluations'];
        $rows = [];

        foreach ($periods as $period) {
            $evaluationCount = TrusteeHasEvaluation::where('evaluation_id', $period->id)->count();
            $statusLabel = $this->getStatusLabel($period->status_id);

            $rows[] = [
                $period->id,
                $period->date_from,
                $period->date_to,
                $statusLabel,
                $evaluationCount,
            ];
        }

        $this->table($headers, $rows);

        $this->newLine();
        $this->info('Use: php artisan evaluation:reset {id}  to reset a specific evaluation period');

        return 0;
    }

    private function getStatusLabel(int $statusId): string
    {
        return match ($statusId) {
            1 => 'In Progress',
            2 => 'Completed',
            3 => 'Pending',
            default => 'Unknown',
        };
    }
}
