<?php

namespace App\Filament\Widgets;

use App\Models\EvaluationPeriod;
use App\Models\TrusteeHasEvaluation;
use App\Filament\Resources\EvaluationPeriods\EvaluationPeriodResource;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class EvaluationStatsOverview extends StatsOverviewWidget
{
    use HasPageShield;

    public $evaluator = null;

    public static function canView(): bool
    {
        // Check if the user has a specific permission using Spatie or standard auth
        return auth()->user()->can('View:EvaluationStatsOverview');
    }

    protected function getStats(): array
    {
        $activeEvaluationPeriod = EvaluationPeriod::where('status_id', 1)->first();

        if (!$activeEvaluationPeriod) {
            return [
                Stat::make('Active Period', 'None')
                    ->color('warning'),
            ];
        }

        // Build base query with role-based filtering
        $baseQuery = TrusteeHasEvaluation::where('evaluation_id', $activeEvaluationPeriod->id);

        // Filter by evaluator if provided
        if ($this->evaluator) {
            $baseQuery->where('evaluator_id', $this->evaluator);
        }

        // Filter by role: executives see all, non-executives see only their own
        $is_evaluator = false;
        if (!get_executive_role(auth()->user()->roles->first()?->name) && !$this->evaluator) {
            $baseQuery->where('evaluator_id', auth()->id());

            $is_evaluator = $baseQuery->where('evaluator_id', auth()->id())->first();

        }

        $totalEvaluations = (clone $baseQuery)->count();
        $submittedEvaluations = (clone $baseQuery)
            ->where('trustee_evaluation_statuses_id', 2)
            ->count();
        $inProgressEvaluations = (clone $baseQuery)
            ->where('trustee_evaluation_statuses_id', 1)
            ->count();
        $pendingEvaluations = (clone $baseQuery)
            ->where('trustee_evaluation_statuses_id', 3)
            ->count();

        $completionPercentage = $totalEvaluations > 0 ? round(($submittedEvaluations / $totalEvaluations) * 100, 1) : 0;

        $viewUrl = EvaluationPeriodResource::getUrl('view', ['record' => $activeEvaluationPeriod->id]);

        return [
            Stat::make('Completion Rate', $completionPercentage . '%')
                ->description($submittedEvaluations . ' of ' . $totalEvaluations . ' submitted')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($completionPercentage >= 75 ? 'success' : ($completionPercentage >= 50 ? 'warning' : 'danger'))
                ->url($viewUrl),

            Stat::make('Total Evaluations', $totalEvaluations)
                ->description('Across all forms')
                ->visible(fn ($is_evaluator) => ($is_evaluator) ? false : true)
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info')
                ->url($viewUrl),

            Stat::make('In Progress', $inProgressEvaluations)
                ->description('Currently being worked on')
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary')
                ->url($viewUrl),

            Stat::make('Pending', $pendingEvaluations)
                ->description('Awaiting action')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('warning')
                ->url($viewUrl),
        ];
    }
}
