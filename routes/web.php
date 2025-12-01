<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Controladores de Autenticación
use App\Http\Controllers\Auth\LoginController;

// Controladores de la Aplicación
use App\Http\Controllers\{
    EDSController, 
    ClienteController, 
    FacturaController, 
    AbonoController,
    InformeController, 
    AuditoriaController, 
    UserController, 
    RoleController,
    CarteraController // Controlador de Cartera
};

/*
|--------------------------------------------------------------------------
| Rutas Públicas (Invitados)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
    
    // Redirección raíz a login
    Route::get('/', function () {
        return redirect()->route('login');
    });
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
    
    // --- DASHBOARD ---
    Route::view('/', 'dashboard')->name('dashboard');

    // --- MÓDULOS OPERATIVOS BÁSICOS ---
    Route::resource('eds', EDSController::class);
    Route::resource('clientes', ClienteController::class);
    
    // --- MÓDULO DE FACTURACIÓN (CUENTAS) ---
    // La ruta de exportar debe ir ANTES del resource para evitar conflictos con {factura}
    Route::get('facturas/exportar', [FacturaController::class, 'export'])->name('facturas.export');
    Route::resource('facturas', FacturaController::class);

    // --- MÓDULO DE ABONOS (CAJA) ---
    // APIs para los buscadores en tiempo real (Alpine.js) usadas en Abonos y Cartera
    Route::get('/api/facturas-pendientes', [AbonoController::class, 'buscarFacturas'])->name('api.facturas.pendientes');
    Route::get('/api/clientes/buscar', [AbonoController::class, 'buscarClientes'])->name('api.clientes.buscar');
    Route::get('/api/clientes/{cliente}/cartera', [AbonoController::class, 'carteraCliente'])->name('api.clientes.cartera');
    
    // CRUD de Abonos
    Route::resource('abonos', AbonoController::class)->only(['index', 'create', 'store', 'destroy']);
    
    // --- MÓDULO DE GESTIÓN DE CARTERA ---
    // Nueva ruta para exportar detalle de UN cliente (debe ir antes del index o export general si hay conflicto, pero aquí no chocan)
    Route::get('cartera/{cliente}/exportar', [CarteraController::class, 'exportarCliente'])->name('cartera.exportar_cliente');
    // Ruta para exportar reporte general
    Route::get('cartera/exportar', [CarteraController::class, 'export'])->name('cartera.export');
    // Ruta principal del Dashboard
    Route::get('cartera', [CarteraController::class, 'index'])->name('cartera.index');

    // --- MÓDULOS ADMINISTRATIVOS ---
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);

    // --- REPORTES Y AUDITORÍA ---
    Route::get('informes/aging', [InformeController::class, 'aging'])->name('informes.aging');
    Route::get('auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
});

/*
|--------------------------------------------------------------------------
| Manejo de Errores (Fallback)
|--------------------------------------------------------------------------
*/
Route::fallback(fn () => response()->view('errors.404', [], 404));