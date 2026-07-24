<?php

namespace App\Filament\Widgets;

use App\Models\EvaluationPeriod;
use App\Models\TrusteeHasEvaluation;
use Filament\Widgets\ChartWidget;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class EvaluatorsPerformanceChart extends ChartWidget
{
    use HasPageShield;

    protected static ?int $sort = 5;

    public static function canView(): bool
    {
        // Check if the user has a specific permission using Spatie or standard auth
        return auth()->user()->can('View:EvaluatorsPerformanceChart');
    }

    public function getHeading(): ?string
    {
        return 'Evaluators Performance';
    }

    protected function getData(): array
    {
        $activeEvaluationPeriod = EvaluationPeriod::where('status_id', 1)->first();

        if (!$activeEvaluationPeriod) {
            return [
                'datasets' => [
                    [
                        'label' => 'No Active Period',
                        'data' => [0],
                        'backgroundColor' => ['#ccc'],
                    ],
                ],
                'labels' => ['N/A'],
            ];
        }

        $query = TrusteeHasEvaluation::where('evaluation_id', $activeEvaluationPeriod->id);

        // Filter by role: executives see all, non-executives see only their own
        if (!get_executive_role(auth()->user()->roles->first()?->name)) {
            $query->where('evaluator_id', auth()->id());
        }

        $evaluatorStats = $query
            ->selectRaw(
                'evaluator_id,
                COUNT(*) as total,
                SUM(CASE WHEN trustee_evaluation_statuses_id = 2 THEN 1 ELSE 0 END) as submitted,
                SUM(CASE WHEN trustee_evaluation_statuses_id = 1 THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN trustee_evaluation_statuses_id = 3 THEN 1 ELSE 0 END) as pending'
            )
            ->groupBy('evaluator_id')
            ->with('evaluator')
            ->orderByDesc('submitted')
            ->limit(10)
            ->get();

        $labels = [];
        $submitted = [];
        $inProgress = [];
        $pending = [];

        foreach ($evaluatorStats as $stat) {
            $labels[] = $stat->evaluator?->first_name . ' ' . $stat->evaluator?->last_name ?? 'Unknown';
            $submitted[] = $stat->submitted ?? 0;
            $inProgress[] = $stat->in_progress ?? 0;
            $pending[] = $stat->pending ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Submitted',
                    'data' => $submitted,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.8)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'In Progress',
                    'data' => $inProgress,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Pending',
                    'data' => $pending,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.8)',
                    'borderColor' => 'rgba(239, 68, 68, 1)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
