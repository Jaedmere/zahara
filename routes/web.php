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
    CarteraController,
    CarteraEdsController
};

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
    Route::get('/', fn () => redirect()->route('login'));
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    
    Route::view('/', 'dashboard')->name('dashboard');

    Route::resource('eds', EDSController::class);
    Route::resource('clientes', ClienteController::class);
    
    Route::get('facturas/exportar', [FacturaController::class, 'export'])->name('facturas.export');
    Route::resource('facturas', FacturaController::class);

    Route::get('/api/facturas-pendientes', [AbonoController::class, 'buscarFacturas'])->name('api.facturas.pendientes');
    Route::get('/api/clientes/buscar', [AbonoController::class, 'buscarClientes'])->name('api.clientes.buscar');
    Route::get('/api/clientes/{cliente}/cartera', [AbonoController::class, 'carteraCliente'])->name('api.clientes.cartera');
    
    Route::resource('abonos', AbonoController::class)->only(['index', 'create', 'store', 'destroy']);
    
    // CARTERA POR CLIENTE
    Route::get('cartera/{cliente}/exportar', [CarteraController::class, 'exportarCliente'])->name('cartera.exportar_cliente');
    Route::get('cartera/exportar', [CarteraController::class, 'export'])->name('cartera.export');
    Route::get('cartera', [CarteraController::class, 'index'])->name('cartera.index');

    // CARTERA POR EDS (DESGLOSADO EDS-CLIENTE)
    Route::get('/api/cartera-eds/{eds}/{cliente}', [CarteraEdsController::class, 'detallePar'])->name('api.cartera_eds.detalle');
    Route::get('cartera-eds/{eds}/{cliente}/exportar', [CarteraEdsController::class, 'exportarPar'])->name('cartera_eds.exportar_par');
    Route::get('cartera-eds/exportar', [CarteraEdsController::class, 'export'])->name('cartera_eds.export');
    Route::get('cartera-eds', [CarteraEdsController::class, 'index'])->name('cartera_eds.index');

    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);

    Route::get('informes/aging', [InformeController::class, 'aging'])->name('informes.aging');
    Route::get('auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
});

Route::fallback(fn () => response()->view('errors.404', [], 404));