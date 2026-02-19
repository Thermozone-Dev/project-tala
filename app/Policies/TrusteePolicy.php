<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Trustee;
use Illuminate\Auth\Access\HandlesAuthorization;

class TrusteePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Trustee');
    }

    public function view(AuthUser $authUser, Trustee $trustee): bool
    {
        return $authUser->can('View:Trustee');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Trustee');
    }

    public function update(AuthUser $authUser, Trustee $trustee): bool
    {
        return $authUser->can('Update:Trustee');
    }

    public function delete(AuthUser $authUser, Trustee $trustee): bool
    {
        return $authUser->can('Delete:Trustee');
    }

    public function restore(AuthUser $authUser, Trustee $trustee): bool
    {
        return $authUser->can('Restore:Trustee');
    }

    public function forceDelete(AuthUser $authUser, Trustee $trustee): bool
    {
        return $authUser->can('ForceDelete:Trustee');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Trustee');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Trustee');
    }

    public function replicate(AuthUser $authUser, Trustee $trustee): bool
    {
        return $authUser->can('Replicate:Trustee');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Trustee');
    }

}