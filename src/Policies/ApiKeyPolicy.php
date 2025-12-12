<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\ApiKey;
use Cartino\Models\User;

class ApiKeyPolicy
{
    /**
     * Perform pre-authorization checks.
     * Super admins can do anything.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->is_super) {
            return true;
        }

        return null;
    }

    /**
     * Determine if the user can view any API keys.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view api_keys');
    }

    /**
     * Determine if the user can view the API key.
     */
    public function view(User $user, ApiKey $apiKey): bool
    {
        return $user->can('view api_keys');
    }

    /**
     * Determine if the user can create API keys.
     */
    public function create(User $user): bool
    {
        return $user->can('create api_keys');
    }

    /**
     * Determine if the user can update the API key.
     */
    public function update(User $user, ApiKey $apiKey): bool
    {
        return $user->can('edit api_keys');
    }

    /**
     * Determine if the user can delete the API key.
     */
    public function delete(User $user, ApiKey $apiKey): bool
    {
        return $user->can('delete api_keys');
    }

    /**
     * Determine if the user can restore the API key.
     */
    public function restore(User $user, ApiKey $apiKey): bool
    {
        return $user->can('delete api_keys');
    }

    /**
     * Determine if the user can permanently delete the API key.
     */
    public function forceDelete(User $user, ApiKey $apiKey): bool
    {
        return $user->can('delete api_keys');
    }
}
