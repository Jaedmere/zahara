<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\{
    EDSController, ClienteController, FacturaController, AbonoController,
    InformeController, AuditoriaController
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

    Route::resource('eds', EDSController::class);
    Route::resource('clientes', ClienteController::class);
    Route::resource('facturas', FacturaController::class);
    Route::resource('abonos', AbonoController::class)->only(['index','create','store','destroy']);

    Route::get('informes/aging', [InformeController::class, 'aging'])->name('informes.aging');
    Route::get('auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
});

// Opcional: captura 404 “bonita”
Route::fallback(fn () => response()->view('errors.404', [], 404));
