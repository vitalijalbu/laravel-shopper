<?php

namespace LaravelShopper\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use LaravelShopper\Models\User;

class ControlPanelPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can access the control panel.
     */
    public function access(User $user): bool
    {
        return $user->can('access-cp');
    }

    /**
     * Determine whether the user can view the dashboard.
     */
    public function viewDashboard(User $user): bool
    {
        return $user->can('view-dashboard');
    }

    /**
     * Determine whether the user can view analytics.
     */
    public function viewAnalytics(User $user): bool
    {
        return $user->can('view-analytics');
    }

    /**
     * Determine whether the user can view reports.
     */
    public function viewReports(User $user): bool
    {
        return $user->can('view-reports');
    }

    /**
     * Determine whether the user can manage settings.
     */
    public function manageSettings(User $user): bool
    {
        return $user->can('view-settings') || $user->can('edit-settings');
    }

    /**
     * Determine whether the user can edit settings.
     */
    public function editSettings(User $user): bool
    {
        return $user->can('edit-settings');
    }

    /**
     * Determine whether the user can manage users.
     */
    public function manageUsers(User $user): bool
    {
        return $user->can('view-users') || $user->can('create-users') || $user->can('edit-users');
    }

    /**
     * Determine whether the user can manage roles and permissions.
     */
    public function manageRoles(User $user): bool
    {
        return $user->can('manage-roles') || $user->can('manage-permissions');
    }
}
