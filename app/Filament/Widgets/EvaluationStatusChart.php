<?php

namespace App\Filament\Widgets;

use App\Models\EvaluationPeriod;
use App\Models\TrusteeHasEvaluation;
use Filament\Widgets\ChartWidget;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class EvaluationStatusChart extends ChartWidget
{
    use HasPageShield;

    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        // Check if the user has a specific permission using Spatie or standard auth
        return auth()->user()->can('View:EvaluationStatusChart');
    }


    public function getHeading(): ?string
    {
        return 'Evaluation Status Distribution';
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

        $statusCounts = $query
            ->selectRaw('trustee_evaluation_statuses_id, COUNT(*) as count')
            ->groupBy('trustee_evaluation_statuses_id')
            ->with('eval_status')
            ->get();

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($statusCounts as $status) {
            $labels[] = $status->eval_status?->name ?? 'Unknown';
            $data[] = $status->count;

            $colorMap = [
                1 => 'rgba(59, 130, 246, 0.8)', // In Progress - blue
                2 => 'rgba(34, 197, 94, 0.8)',  // Submitted - green
                3 => 'rgba(239, 68, 68, 0.8)',  // Pending - red
                4 => 'rgba(107, 114, 128, 0.8)', // For Review - gray
                5 => 'rgba(168, 85, 247, 0.8)', // Reviewed - purple
            ];

            $colors[] = $colorMap[$status->trustee_evaluation_statuses_id] ?? 'rgba(156, 163, 175, 0.8)';
        }

        return [
            'datasets' => [
                [
                    'label' => 'Evaluations',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => array_map(fn ($color) => str_replace('0.8', '1', $color), $colors),
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
