<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class StorePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Store');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('View:Store');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Store');
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->can('Update:Store');
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:Store');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Store');
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->can('Restore:Store');
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDelete:Store');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Store');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Store');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->can('Replicate:Store');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Store');
    }
}
