<?php

namespace App\Filament\Resources\EvaluationPeriods\Pages;

use App\Filament\Resources\EvaluationPeriods\EvaluationPeriodResource;
use App\Models\Committee;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateEvaluationPeriod extends CreateRecord
{
    protected static string $resource = EvaluationPeriodResource::class;

    public function handleRecordCreation(array $data): Model
    {

        $data['created_by'] = auth()->id(); // Set the created_by field to the current user's ID
        $data['status_id'] = 1; // Set the created_by field to the current user's ID

        return parent::handleRecordCreation($data);
    }

    protected function afterCreate(): void
    {

        Committee::all()->each(function ($committee) {
            $committee->committee_has_trustees;
            $commmitee_member = $committee->committee_has_trustees->groupBy('user_id')->map(function ($item, $key) {
                return $item->first();
            });
            foreach( $commmitee_member as $member){
                $committee->committee_has_trustees->each(function ($committeeHasTrustee) use ($committee, $member) {
                    if($committeeHasTrustee->user_id == $member->user_id){

                        $test = [
                            'evaluation_id' =>  $this->record->id,
                            'ef_id' => 8,
                            'committee_id' => $committee->id,
                            'member_id' => null,
                            'evaluator_id' => $member->user_id,
                        ];

                        $this->record->assignments()->create($test);

                        $test = [
                            'evaluation_id' => $this->record->id,
                            'ef_id' => 9,
                            'committee_id' => $committee->id,
                            'member_id' => null,
                            'evaluator_id' => $member->user_id,
                        ];
                        $this->record->assignments()->create($test);

                    }
                    else{
                        $test = [
                            'evaluation_id' => $this->record->id,
                            'ef_id' => get_eval_form_by_role($committeeHasTrustee->role->name),
                            'committee_id' => $committee->id,
                            'member_id' => $committeeHasTrustee->user_id,
                            'evaluator_id' => $member->user_id,
                        ];
                        $this->record->assignments()->create($test);
                    }

                });

            }

        });

    }
}
