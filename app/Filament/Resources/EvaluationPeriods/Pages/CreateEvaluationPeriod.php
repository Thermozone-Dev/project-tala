<?php

namespace App\Filament\Resources\EvaluationPeriods\Pages;

use App\Filament\Resources\EvaluationPeriods\EvaluationPeriodResource;
use App\Models\Committee;
use App\Models\EvaluationPeriod;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateEvaluationPeriod extends CreateRecord
{
    protected static string $resource = EvaluationPeriodResource::class;


    public function mount(): void{



        if(EvaluationPeriod::where('status_id', 1)->exists()){
            Notification::make()
                ->title('An active evaluation period already exists.')
                ->body('Please end the current evaluation period before starting a new one.')
                ->danger()
                ->send();

            redirect($this->getResource()::getUrl('index'));
        }
    }


    public function handleRecordCreation(array $data): Model
    {

        $data['created_by'] = auth()->id(); // Set the created_by field to the current user's ID
        $data['status_id'] = 1; // Set the created_by field to the current user's ID

        return parent::handleRecordCreation($data);
    }

    protected function afterCreate(): void
    {
        try {
            DB::transaction(function () {
                // 1. Form 8 (Self Assessment) for all board members
                $board_members = User::role(get_board_members())->get();
                foreach ($board_members as $member) {
                    $this->record->assignments()->create([
                        'ef_id' => 8,
                        'committee_id' => null,
                        'member_id' => null,
                        'evaluator_id' => $member->id,
                    ]);
                }

                // 2. Committee assignments: Form 9 for all + Form 7 (C7) for evaluating LRPs
                $committees = Committee::with('committee_has_trustees.role')->get();
                foreach ($committees as $committee) {
                    $committee_members = $committee->committee_has_trustees
                        ->groupBy('user_id')
                        ->map(fn($items) => $items->first());

                    // Find all LRPs in this committee
                    $lrps = $committee->committee_has_trustees
                        ->filter(fn($member) => strtolower($member->role->name) === 'lead resource person');

                    foreach ($committee_members as $evaluator) {
                        // Form 9: Committee Self Assessment for all members
                        $this->record->assignments()->create([
                            'ef_id' => 9,
                            'committee_id' => $committee->id,
                            'member_id' => null,
                            'evaluator_id' => $evaluator->user_id,
                        ]);

                        // Form 7 (C7): Each member evaluates all LRPs in the committee
                        foreach ($lrps as $lrp) {
                            // Don't create self-evaluation
                            if ($evaluator->user_id === $lrp->user_id) {
                                continue;
                            }

                            $this->record->assignments()->create([
                                'ef_id' => 7,
                                'committee_id' => $committee->id,
                                'member_id' => $lrp->user_id,
                                'evaluator_id' => $evaluator->user_id,
                            ]);
                        }
                    }
                }

                // 3. Cross-evaluation: Each board member evaluates every other board member
                $board_members_with_roles = User::role(get_board_members())
                    ->with('roles')
                    ->get();

                foreach ($board_members_with_roles as $evaluator) {
                    foreach ($board_members_with_roles as $evaluatee) {
                        // Don't create self-evaluation
                        if ($evaluator->getKey() === $evaluatee->getKey()) {
                            continue;
                        }

                        $evaluatee_role = $evaluatee->roles()->first();
                        if (!$evaluatee_role) {
                            continue;
                        }

                        $eval_form = get_eval_form_by_role($evaluatee_role->name);
                        if (!$eval_form) {
                            continue;
                        }

                        $this->record->assignments()->create([
                            'ef_id' => $eval_form,
                            'committee_id' => null,
                            'member_id' => $evaluatee->getKey(),
                            'evaluator_id' => $evaluator->getKey(),
                        ]);
                    }
                }
            });

            Notification::make()
                ->title('Success')
                ->body('Evaluation period created and all assignments delegated successfully.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Failed to create evaluation period: ' . $e->getMessage())
                ->danger()
                ->send();

            throw $e;
        }
    }
}
