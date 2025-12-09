<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Controladores de Autenticación
use App\Http\Controllers\Auth\LoginController;

// Controladores de la Aplicación
use App\Http\Controllers\{
    DashboardController,     // Dashboard BI
    EDSController, 
    ClienteController, 
    FacturaController, 
    AbonoController,
    InformeController, 
    AuditoriaController, 
    UserController, 
    RoleController,
    CarteraController,       // Consolidado por Cliente
    CarteraEdsController,    // Consolidado por EDS
    CarteraCuentasController,// Consolidado por Cuenta
    SeguimientoController    // CRM / Seguimientos
};

/*
|--------------------------------------------------------------------------
| Rutas Públicas (Invitados)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
    
    // Redirección raíz a login si no está autenticado
    Route::get('/', fn () => redirect()->route('login'));
});

/*
|--------------------------------------------------------------------------
| Rutas de Autenticación (Logout)
|--------------------------------------------------------------------------
*/
Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Rutas Protegidas (Aplicación Principal)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    
    /*
    |-------------------------------
    | DASHBOARD BI (Principal)
    |-------------------------------
    */
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard-data', [DashboardController::class, 'getData'])->name('api.dashboard.data');
    Route::get('/dashboard/exportar', [DashboardController::class, 'exportDashboard'])->name('dashboard.export');

    /*
    |-------------------------------
    | MÓDULOS OPERATIVOS BÁSICOS
    |-------------------------------
    */
    Route::resource('eds', EDSController::class);
    Route::resource('clientes', ClienteController::class);
    
    /*
    |-------------------------------
    | MÓDULO DE FACTURACIÓN (CUENTAS)
    |-------------------------------
    | La ruta de exportar debe ir ANTES del resource para evitar conflictos con {factura}
    */
    Route::get('facturas/exportar', [FacturaController::class, 'export'])->name('facturas.export');
    Route::resource('facturas', FacturaController::class);

    /*
    |-------------------------------
    | MÓDULO DE ABONOS (CAJA)
    |-------------------------------
    */
    // APIs para los buscadores en tiempo real (Alpine.js) usadas en Abonos y Cartera
    Route::get('/api/facturas-pendientes', [AbonoController::class, 'buscarFacturas'])->name('api.facturas.pendientes');
    Route::get('/api/clientes/buscar', [AbonoController::class, 'buscarClientes'])->name('api.clientes.buscar');
    Route::get('/api/clientes/{cliente}/cartera', [AbonoController::class, 'carteraCliente'])->name('api.clientes.cartera');
    
    // CRUD de Abonos
    Route::resource('abonos', AbonoController::class)->only(['index', 'create', 'store', 'destroy']);
    
    /*
    |-------------------------------
    | GESTIÓN DE CARTERA: CONSOLIDADO POR CLIENTE
    |-------------------------------
    */
    Route::get('cartera/{cliente}/exportar', [CarteraController::class, 'exportarCliente'])->name('cartera.exportar_cliente');
    Route::get('cartera/exportar', [CarteraController::class, 'export'])->name('cartera.export');
    Route::get('cartera', [CarteraController::class, 'index'])->name('cartera.index');

    /*
    |-------------------------------
    | GESTIÓN DE CARTERA: CONSOLIDADO POR EDS
    |-------------------------------
    */
    Route::get('/api/cartera-eds/{eds}/{cliente}', [CarteraEdsController::class, 'detallePar'])->name('api.cartera_eds.detalle');
    Route::get('cartera-eds/{eds}/{cliente}/exportar', [CarteraEdsController::class, 'exportarPar'])->name('cartera_eds.exportar_par');
    Route::get('cartera-eds/exportar', [CarteraEdsController::class, 'export'])->name('cartera_eds.export');
    Route::get('cartera-eds', [CarteraEdsController::class, 'index'])->name('cartera_eds.index');

    /*
    |-------------------------------
    | GESTIÓN DE CARTERA: CONSOLIDADO POR CUENTA
    |-------------------------------
    */
    Route::get('/api/cartera-cuentas/{factura}', [CarteraCuentasController::class, 'detalleCuenta'])->name('api.cartera_cuentas.detalle');
    Route::get('cartera-cuentas/{factura}/exportar', [CarteraCuentasController::class, 'exportarCuenta'])->name('cartera_cuentas.exportar_individual');
    Route::get('cartera-cuentas/exportar', [CarteraCuentasController::class, 'export'])->name('cartera_cuentas.export');
    Route::get('cartera-cuentas', [CarteraCuentasController::class, 'index'])->name('cartera_cuentas.index');

    /*
    |-------------------------------
    | NUEVO MÓDULO: SEGUIMIENTOS (CRM)
    |-------------------------------
    */
    Route::get('seguimientos', [SeguimientoController::class, 'index'])->name('seguimientos.index');
    Route::post('seguimientos', [SeguimientoController::class, 'store'])->name('seguimientos.store');
    Route::put('seguimientos/{seguimiento}', [SeguimientoController::class, 'update'])->name('seguimientos.update');
    Route::delete('seguimientos/{seguimiento}', [SeguimientoController::class, 'destroy'])->name('seguimientos.destroy');
    
    Route::get('api/seguimientos/{cliente}/historial', [SeguimientoController::class, 'historial'])->name('api.seguimientos.historial');
    Route::post('api/seguimientos/{seguimiento}/check', [SeguimientoController::class, 'check'])->name('api.seguimientos.check');
    Route::post('api/seguimientos/{seguimiento}/cancel', [SeguimientoController::class, 'cancel'])->name('api.seguimientos.cancel');

    /*
    |-------------------------------
    | NUEVO: NOTIFICACIONES
    |-------------------------------
    */
    Route::get('notificaciones', [SeguimientoController::class, 'notificacionesIndex'])->name('notificaciones.index');
    Route::get('api/notificaciones/conteo', [SeguimientoController::class, 'conteoAlertas'])->name('api.notificaciones.conteo');

    /*
    |-------------------------------
    | MÓDULOS ADMINISTRATIVOS
    |-------------------------------
    */
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);

    /*
    |-------------------------------
    | REPORTES Y AUDITORÍA
    |-------------------------------
    */
    Route::prefix('informes')->name('informes.')->group(function () {
        // Extracto por cliente
        Route::get('/extracto', [InformeController::class, 'extracto'])
            ->name('extracto');

        // Cartera por edades
        Route::get('/cartera-edades', [InformeController::class, 'carteraEdades'])
            ->name('cartera_edades');

        // Estado de cuentas a corte
        Route::get('/estado-cuentas', [InformeController::class, 'estadoCuentas'])
            ->name('estado_cuentas');

        // Export CSV de estado de cuentas a corte
        Route::get('/estado-cuentas/export', [InformeController::class, 'estadoCuentasExport'])
            ->name('estado_cuentas_export');
    });

    Route::get('auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
});

/*
|--------------------------------------------------------------------------
| Manejo de Errores (Fallback)
|--------------------------------------------------------------------------
*/
Route::fallback(fn () => response()->view('errors.404', [], 404));
