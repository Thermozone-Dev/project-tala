<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\EvaluationPeriod;
use Illuminate\Auth\Access\HandlesAuthorization;

class EvaluationPeriodPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EvaluationPeriod');
    }

    public function view(AuthUser $authUser, EvaluationPeriod $evaluationPeriod): bool
    {
        return $authUser->can('View:EvaluationPeriod');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EvaluationPeriod');
    }

    public function update(AuthUser $authUser, EvaluationPeriod $evaluationPeriod): bool
    {
        return $authUser->can('Update:EvaluationPeriod');
    }

    public function delete(AuthUser $authUser, EvaluationPeriod $evaluationPeriod): bool
    {
        return $authUser->can('Delete:EvaluationPeriod');
    }

    public function restore(AuthUser $authUser, EvaluationPeriod $evaluationPeriod): bool
    {
        return $authUser->can('Restore:EvaluationPeriod');
    }

    public function forceDelete(AuthUser $authUser, EvaluationPeriod $evaluationPeriod): bool
    {
        return $authUser->can('ForceDelete:EvaluationPeriod');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EvaluationPeriod');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EvaluationPeriod');
    }

    public function replicate(AuthUser $authUser, EvaluationPeriod $evaluationPeriod): bool
    {
        return $authUser->can('Replicate:EvaluationPeriod');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EvaluationPeriod');
    }

}