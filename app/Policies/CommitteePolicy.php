<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Committee;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommitteePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Committee');
    }

    public function view(AuthUser $authUser, Committee $committee): bool
    {
        return $authUser->can('View:Committee');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Committee');
    }

    public function update(AuthUser $authUser, Committee $committee): bool
    {
        return $authUser->can('Update:Committee');
    }

    public function delete(AuthUser $authUser, Committee $committee): bool
    {
        return $authUser->can('Delete:Committee');
    }

    public function restore(AuthUser $authUser, Committee $committee): bool
    {
        return $authUser->can('Restore:Committee');
    }

    public function forceDelete(AuthUser $authUser, Committee $committee): bool
    {
        return $authUser->can('ForceDelete:Committee');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Committee');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Committee');
    }

    public function replicate(AuthUser $authUser, Committee $committee): bool
    {
        return $authUser->can('Replicate:Committee');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Committee');
    }

    public function fullAccess(AuthUser $authUser): bool
    {
        return $authUser->can('FullAccess:Committee');
    }

}
