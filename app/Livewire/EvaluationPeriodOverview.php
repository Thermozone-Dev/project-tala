<?php

namespace App\Livewire;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\EvaluationPeriod;
class EvaluationPeriodOverview extends StatsOverviewWidget
{

    public $evaluation_period_id;
    public $trustee_id;



    protected function getStats(): array
    {
        $percentage = 0;
        $submitted_evaluation = 0;
        $total_evaluations = 0;

        if($this->evaluation_period_id){
            $evaluation_period = EvaluationPeriod::find($this->evaluation_period_id);

            // Build base query
            $baseQuery = $evaluation_period->assignments();

            // Filter by role: executives see all assignments, non-executives see only their own
            if (!get_executive_role(auth()->user()->roles->first()?->name)) {
                $baseQuery = $baseQuery->where('evaluator_id', auth()->id());
            }

            if($this->trustee_id){
                $baseQuery = $baseQuery->where('evaluator_id', $this->trustee_id);
            }

            // Get counts using fresh queries to avoid scope interference
            $in_progress = (clone $baseQuery)->where('trustee_evaluation_statuses_id', 1)->count();
            $pending_evaluations = (clone $baseQuery)->where('trustee_evaluation_statuses_id', 3)->count();
            $total_evaluations = (clone $baseQuery)->count();

            $submitted_evaluation = $total_evaluations - ($pending_evaluations + $in_progress);
            $percentage = (($total_evaluations - ($pending_evaluations + $in_progress)) / (($total_evaluations <= 0) ? 1 : $total_evaluations)) * 100;
            $percentage = round($percentage, 1);
        }
        // dd($evaluation_period);
        return [
            Stat::make('Evaluation Completion Percentage', $percentage.' %')
                ->color('primary'),

            Stat::make('Submitted Evaluation', $submitted_evaluation.' / '.$total_evaluations)
                ->color('success'),
            Stat::make('Completed Evaluation', $submitted_evaluation),
        ];
    }

    protected function getHeading(): ?string
    {

        $evaluation_period = EvaluationPeriod::find($this->evaluation_period_id);
        $heading = 'Evaluation Overview: '. (($evaluation_period) ? $evaluation_period->formatted_coverage : 'Overall');
        return $heading;
    }

}
