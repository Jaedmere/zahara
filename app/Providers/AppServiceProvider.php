<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator; // <--- Necesario para cambiar el diseño de la paginación
use Illuminate\Support\Facades\Gate; // <--- Necesario para la seguridad
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. ACTIVAR PAGINACIÓN RESPONSIVE (Móvil/PC)
        // Le decimos a Laravel que use nuestra vista 'zahara.blade.php' en lugar de la fea por defecto.
        Paginator::defaultView('vendor.pagination.zahara');
        Paginator::defaultSimpleView('vendor.pagination.zahara');

        // 2. DEFINIR REGLAS DE ACCESO (Seguridad)
        // Definimos la regla 'acceso'. Si el usuario no tiene el permiso en su JSON, no entra.
        Gate::define('acceso', function (User $user, string $modulo) {
            
            // Si el usuario está desactivado, no tiene acceso a nada
            if (!$user->activo) {
                return false;
            }

            // Obtenemos sus permisos (ej: ['eds', 'facturas'])
            $permisos = $user->role->permisos_json ?? [];

            // Verificamos si el módulo que pide está en su lista
            return in_array($modulo, $permisos);
        });
    }
}