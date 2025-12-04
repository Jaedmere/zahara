<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Controladores de Autenticación
use App\Http\Controllers\Auth\LoginController;

// Controladores de la Aplicación
use App\Http\Controllers\{
    DashboardController,
    EDSController, 
    ClienteController, 
    FacturaController, 
    AbonoController,
    InformeController, 
    AuditoriaController, 
    UserController, 
    RoleController,
    CarteraController,
    CarteraEdsController,
    CarteraCuentasController,
    SeguimientoController
};

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
    Route::get('/', fn () => redirect()->route('login'));
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    
    // --- DASHBOARD ---
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard-data', [DashboardController::class, 'getData'])->name('api.dashboard.data');
    Route::get('/dashboard/exportar', [DashboardController::class, 'exportDashboard'])->name('dashboard.export');

    // --- OPERACIÓN ---
    Route::resource('eds', EDSController::class);
    Route::resource('clientes', ClienteController::class);
    
    // --- FACTURACIÓN ---
    Route::get('facturas/exportar', [FacturaController::class, 'export'])->name('facturas.export');
    Route::resource('facturas', FacturaController::class);

    // --- RECAUDO ---
    Route::get('/api/facturas-pendientes', [AbonoController::class, 'buscarFacturas'])->name('api.facturas.pendientes');
    Route::get('/api/clientes/buscar', [AbonoController::class, 'buscarClientes'])->name('api.clientes.buscar');
    Route::get('/api/clientes/{cliente}/cartera', [AbonoController::class, 'carteraCliente'])->name('api.clientes.cartera');
    Route::resource('abonos', AbonoController::class)->only(['index', 'create', 'store', 'destroy']);
    
    // --- CARTERA ---
    Route::get('cartera/{cliente}/exportar', [CarteraController::class, 'exportarCliente'])->name('cartera.exportar_cliente');
    Route::get('cartera/exportar', [CarteraController::class, 'export'])->name('cartera.export');
    Route::get('cartera', [CarteraController::class, 'index'])->name('cartera.index');

    Route::get('/api/cartera-eds/{eds}/{cliente}', [CarteraEdsController::class, 'detallePar'])->name('api.cartera_eds.detalle');
    Route::get('cartera-eds/{eds}/{cliente}/exportar', [CarteraEdsController::class, 'exportarPar'])->name('cartera_eds.exportar_par');
    Route::get('cartera-eds/exportar', [CarteraEdsController::class, 'export'])->name('cartera_eds.export');
    Route::get('cartera-eds', [CarteraEdsController::class, 'index'])->name('cartera_eds.index');

    Route::get('/api/cartera-cuentas/{factura}', [CarteraCuentasController::class, 'detalleCuenta'])->name('api.cartera_cuentas.detalle');
    Route::get('cartera-cuentas/{factura}/exportar', [CarteraCuentasController::class, 'exportarCuenta'])->name('cartera_cuentas.exportar_individual');
    Route::get('cartera-cuentas/exportar', [CarteraCuentasController::class, 'export'])->name('cartera_cuentas.export');
    Route::get('cartera-cuentas', [CarteraCuentasController::class, 'index'])->name('cartera_cuentas.index');

    // --- SEGUIMIENTOS (CRM) ---
    Route::get('seguimientos', [SeguimientoController::class, 'index'])->name('seguimientos.index');
    Route::post('seguimientos', [SeguimientoController::class, 'store'])->name('seguimientos.store');
    Route::put('seguimientos/{seguimiento}', [SeguimientoController::class, 'update'])->name('seguimientos.update'); // NUEVA
    Route::delete('seguimientos/{seguimiento}', [SeguimientoController::class, 'destroy'])->name('seguimientos.destroy'); // NUEVA
    
    Route::get('api/seguimientos/{cliente}/historial', [SeguimientoController::class, 'historial'])->name('api.seguimientos.historial');
    Route::post('api/seguimientos/{seguimiento}/check', [SeguimientoController::class, 'check'])->name('api.seguimientos.check');

    // --- ADMIN ---
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);

    Route::get('informes/aging', [InformeController::class, 'aging'])->name('informes.aging');
    Route::get('auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
});

Route::fallback(fn () => response()->view('errors.404', [], 404));