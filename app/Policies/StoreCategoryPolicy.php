<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\StoreCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class StoreCategoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:StoreCategory');
    }

    public function view(AuthUser $authUser, StoreCategory $storeCategory): bool
    {
        return $authUser->can('View:StoreCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:StoreCategory');
    }

    public function update(AuthUser $authUser, StoreCategory $storeCategory): bool
    {
        return $authUser->can('Update:StoreCategory');
    }

    public function delete(AuthUser $authUser, StoreCategory $storeCategory): bool
    {
        return $authUser->can('Delete:StoreCategory');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:StoreCategory');
    }

    public function restore(AuthUser $authUser, StoreCategory $storeCategory): bool
    {
        return $authUser->can('Restore:StoreCategory');
    }

    public function forceDelete(AuthUser $authUser, StoreCategory $storeCategory): bool
    {
        return $authUser->can('ForceDelete:StoreCategory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:StoreCategory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:StoreCategory');
    }

    public function replicate(AuthUser $authUser, StoreCategory $storeCategory): bool
    {
        return $authUser->can('Replicate:StoreCategory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:StoreCategory');
    }

}