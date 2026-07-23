<?php

namespace App\Filament\Traits;

use App\Filament\Resources\EvaluationPeriods\EvaluationPeriodResource;
use App\Models\EvaluationPeriod;
use App\Models\TrusteeHasEvaluation;
use Filament\Notifications\Notification;

trait HandlesEvaluationErrors
{
    /**
     * Verify evaluation period exists and check permissions for deleted records
     */
    protected function verifyEvaluationPeriodExists($periodId): bool
    {
        if (!$periodId) {
            return true;
        }

        $period = EvaluationPeriod::withTrashed()->find($periodId);

        if (!$period) {
            Notification::make()
                ->title('Evaluation Period Not Found')
                ->danger()
                ->body('This evaluation period has been deleted or does not exist.')
                ->send();

            redirect(EvaluationPeriodResource::getUrl('index'));
            return false;
        }

        // If record is deleted, only executives can access it
        if ($period->trashed()) {
            $userRole = auth()->user()->roles->first()?->name;
            if (!get_executive_role($userRole)) {
                Notification::make()
                    ->title('Access Denied')
                    ->danger()
                    ->body('You do not have permission to access deleted evaluation periods.')
                    ->send();

                redirect(EvaluationPeriodResource::getUrl('index'));
                return false;
            }
        }

        return true;
    }

    /**
     * Verify evaluation record exists and redirect if not found
     */
    protected function verifyEvaluationRecordExists($recordId): bool
    {
        if (!$recordId) {
            return true;
        }

        $record = TrusteeHasEvaluation::find($recordId);

        if (!$record) {
            Notification::make()
                ->title('Evaluation Record Not Found')
                ->danger()
                ->body('This evaluation record has been deleted or does not exist.')
                ->send();

            redirect(EvaluationPeriodResource::getUrl('index'));
            return false;
        }

        return true;
    }

    /**
     * Safely retrieve a TrusteeHasEvaluation record and show error if not found
     */
    protected function getEvaluationRecordOrFail(?int $recordId)
    {
        try {
            $record = TrusteeHasEvaluation::find($recordId);

            if (!$record) {
                Notification::make()
                    ->title('Error')
                    ->danger()
                    ->body('Evaluation record not found.')
                    ->send();

                redirect(EvaluationPeriodResource::getUrl('index'))->send();
                exit;
            }

            return $record;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error Loading Evaluation')
                ->danger()
                ->body('An error occurred: ' . $e->getMessage())
                ->send();

            redirect(EvaluationPeriodResource::getUrl('index'))->send();
            exit;
        }
    }

    /**
     * Safely retrieve an EvaluationPeriod record and show error if not found
     */
    protected function getEvaluationPeriodOrFail(?int $periodId)
    {
        try {
            $period = EvaluationPeriod::find($periodId);

            if (!$period) {
                Notification::make()
                    ->title('Error')
                    ->danger()
                    ->body('Evaluation period not found or has been deleted.')
                    ->send();

                redirect(EvaluationPeriodResource::getUrl('index'))->send();
                exit;
            }

            return $period;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error Loading Evaluation Period')
                ->danger()
                ->body('An error occurred: ' . $e->getMessage())
                ->send();

            redirect(EvaluationPeriodResource::getUrl('index'))->send();
            exit;
        }
    }
}
