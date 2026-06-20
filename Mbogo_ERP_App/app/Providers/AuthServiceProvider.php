<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot()
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability) {

            // Admin ana access zote
            if ($user->role === 'Admin') {
                return true;
            }

            $hasPermission = DB::table('users_roles')
                ->join('roles', 'roles.id', '=', 'users_roles.role_id')
                ->join('roles_permissions', 'roles_permissions.role_id', '=', 'roles.id')
                ->join('permissions', 'permissions.id', '=', 'roles_permissions.permission_id')
                ->where('users_roles.user_id', $user->id)
                ->where('permissions.slug', $ability)
                ->exists();

            return $hasPermission ? true : null;
        });
    }
}