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
            $evaluation = $evaluation_period->assignments();
            if($this->trustee_id){
                $evaluation = $evaluation->where('evaluator_id', $this->trustee_id);
            }
            $total_evaluations = $evaluation->count();
            $pending_evaluations = $evaluation->pending()->count();
            $submitted_evaluation = $total_evaluations - $pending_evaluations;
            $percentage = (($total_evaluations - $pending_evaluations) / (($total_evaluations <= 0) ? 1 : $total_evaluations)) * 100;
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
