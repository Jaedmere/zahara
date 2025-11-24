<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('is-admin', function (User $user) {
            return optional($user->role)->nombre === 'Administrador';
        });

        Gate::define('can-manage-eds', function (User $user, $edsId) {
            return $user->role && in_array($user->role->nombre, ['Administrador','Jefe de Estación'])
                && $user->eds()->where('eds.id', $edsId)->exists();
        });

        Gate::define('can-register-payments', function (User $user) {
            return $user->role && in_array($user->role->nombre, ['Administrador','Analista Cartera','Jefe de Estación']);
        });
    }
}
