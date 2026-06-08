<?php

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class BoardOfTrusteePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BoardOfTrustee');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('View:BoardOfTrustee');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BoardOfTrustee');
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->can('Update:BoardOfTrustee');
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:BoardOfTrustee');
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->can('Restore:BoardOfTrustee');
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDelete:BoardOfTrustee');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BoardOfTrustee');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BoardOfTrustee');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->can('Replicate:BoardOfTrustee');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BoardOfTrustee');
    }

}