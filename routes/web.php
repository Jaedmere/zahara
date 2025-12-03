<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\{
    EDSController, 
    ClienteController, 
    FacturaController, 
    AbonoController,
    InformeController, 
    AuditoriaController, 
    UserController, 
    RoleController,
    CarteraController,    // Por Cliente
    CarteraEdsController, // Por EDS
    CarteraCuentasController // <--- NUEVO: Por Cuenta
};

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
    Route::get('/', fn () => redirect()->route('login'));
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    
    Route::view('/', 'dashboard')->name('dashboard');

    // --- MÓDULOS OPERATIVOS ---
    Route::resource('eds', EDSController::class);
    Route::resource('clientes', ClienteController::class);
    
    Route::get('facturas/exportar', [FacturaController::class, 'export'])->name('facturas.export');
    Route::resource('facturas', FacturaController::class);

    Route::get('/api/facturas-pendientes', [AbonoController::class, 'buscarFacturas'])->name('api.facturas.pendientes');
    Route::get('/api/clientes/buscar', [AbonoController::class, 'buscarClientes'])->name('api.clientes.buscar');
    Route::get('/api/clientes/{cliente}/cartera', [AbonoController::class, 'carteraCliente'])->name('api.clientes.cartera');
    Route::resource('abonos', AbonoController::class)->only(['index', 'create', 'store', 'destroy']);
    
    // --- CARTERA (CLIENTES) ---
    Route::get('cartera/{cliente}/exportar', [CarteraController::class, 'exportarCliente'])->name('cartera.exportar_cliente');
    Route::get('cartera/exportar', [CarteraController::class, 'export'])->name('cartera.export');
    Route::get('cartera', [CarteraController::class, 'index'])->name('cartera.index');

    // --- CARTERA (EDS) ---
    Route::get('/api/cartera-eds/{eds}/{cliente}', [CarteraEdsController::class, 'detallePar'])->name('api.cartera_eds.detalle');
    Route::get('cartera-eds/{eds}/{cliente}/exportar', [CarteraEdsController::class, 'exportarPar'])->name('cartera_eds.exportar_par');
    Route::get('cartera-eds/exportar', [CarteraEdsController::class, 'export'])->name('cartera_eds.export');
    Route::get('cartera-eds', [CarteraEdsController::class, 'index'])->name('cartera_eds.index');

    // --- CARTERA (CUENTAS - NUEVO) ---
    // API para detalle de una sola factura (Modal)
    Route::get('/api/cartera-cuentas/{factura}', [CarteraCuentasController::class, 'detalleCuenta'])->name('api.cartera_cuentas.detalle');
    // Exportar una sola factura (Modal)
    Route::get('cartera-cuentas/{factura}/exportar', [CarteraCuentasController::class, 'exportarCuenta'])->name('cartera_cuentas.exportar_individual');
    // Exportar listado general
    Route::get('cartera-cuentas/exportar', [CarteraCuentasController::class, 'export'])->name('cartera_cuentas.export');
    // Vista Principal
    Route::get('cartera-cuentas', [CarteraCuentasController::class, 'index'])->name('cartera_cuentas.index');

    // --- OTROS ---
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::get('informes/aging', [InformeController::class, 'aging'])->name('informes.aging');
    Route::get('auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
});

Route::fallback(fn () => response()->view('errors.404', [], 404));