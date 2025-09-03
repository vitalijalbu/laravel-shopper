<?php

namespace Shopper\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Shopper\Models\User;
use Shopper\Models\Brand;

class BrandPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view-brands');
    }

    public function view(User $user, Brand $brand): bool
    {
        return $user->can('view-brands');
    }

    public function create(User $user): bool
    {
        return $user->can('create-brands');
    }

    public function update(User $user, Brand $brand): bool
    {
        return $user->can('edit-brands');
    }

    public function delete(User $user, Brand $brand): bool
    {
        return $user->can('delete-brands') && !$brand->hasProducts();
    }

    public function restore(User $user, Brand $brand): bool
    {
        return $user->can('restore-brands');
    }

    public function forceDelete(User $user, Brand $brand): bool
    {
        return $user->can('force-delete-brands');
    }
}
