<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\{
    EDSController, 
    ClienteController, 
    FacturaController, 
    AbonoController,
    InformeController, 
    AuditoriaController, 
    UserController, 
    RoleController
};

// --- Auth (público) ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

// --- Logout (protegido) ---
Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// --- App (protegido) ---
Route::middleware('auth')->group(function () {
    
    Route::view('/', 'dashboard')->name('dashboard');

    // Módulos Operativos
    Route::resource('eds', EDSController::class);
    Route::resource('clientes', ClienteController::class);
    
    // --- FACTURACIÓN ---
    // IMPORTANTE: Esta ruta va ANTES del resource para evitar conflictos
    Route::get('facturas/exportar', [FacturaController::class, 'export'])->name('facturas.export');
    Route::resource('facturas', FacturaController::class);

    // Módulo de Abonos
    Route::get('/api/facturas-pendientes', [AbonoController::class, 'buscarFacturas'])->name('api.facturas.pendientes');
    Route::resource('abonos', AbonoController::class)->only(['index', 'create', 'store', 'destroy']);
    
    // Módulos Administrativos
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);

    // Reportes
    Route::get('informes/aging', [InformeController::class, 'aging'])->name('informes.aging');
    Route::get('auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
});

Route::fallback(fn () => response()->view('errors.404', [], 404));