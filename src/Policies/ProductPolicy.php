<?php

namespace LaravelShopper\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use LaravelShopper\Models\User;

class ProductPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-products');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, $product): bool
    {
        return $user->can('view-products');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create-products');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, $product): bool
    {
        return $user->can('edit-products');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, $product): bool
    {
        return $user->can('delete-products');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, $product): bool
    {
        return $user->can('edit-products');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, $product): bool
    {
        return $user->can('delete-products');
    }
}
