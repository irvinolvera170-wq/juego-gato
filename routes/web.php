<?php

use App\Http\Controllers\AdminPartidaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\PartidaController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    Route::get('/registro', [RegistroController::class, 'showRegister'])->name('registro');
    Route::post('/registro', [RegistroController::class, 'register'])
        ->middleware('throttle:10,1')
        ->name('registro.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Cada rol aterriza en su pantalla
    Route::get('/home', function () {
        return auth()->user()->esAdmin()
            ? redirect()->route('admin.partidas.index')
            : redirect()->route('partidas.index');
    })->name('home');

    // Solo jugador: lobby, historial y crear partida
    Route::middleware('role:jugador')->group(function () {
        Route::get('/partidas', [PartidaController::class, 'index'])->name('partidas.index');
        Route::get('/lobby/contenido', [PartidaController::class, 'lobby'])->name('partidas.lobby');
        Route::get('/mis-partidas', [PartidaController::class, 'historial'])->name('partidas.historial');
        Route::post('/partidas', [PartidaController::class, 'store'])->name('partidas.store');
    });

    // Solo jugador: unirse, mover y abandonar
    Route::middleware('role:jugador')->where(['partida' => '[0-9]+'])->group(function () {
        Route::post('/partidas/{partida}/unirse', [PartidaController::class, 'join'])->name('partidas.join');
        Route::post('/partidas/{partida}/movimientos', [MovimientoController::class, 'store'])
            ->middleware('throttle:60,1')
            ->name('partidas.mover');
        Route::post('/partidas/{partida}/abandonar', [PartidaController::class, 'abandon'])->name('partidas.abandon');
    });

    // Jugador y administrador: ver el tablero y consultar su estado (polling)
    Route::middleware('role:admin,jugador')->where(['partida' => '[0-9]+'])->group(function () {
        Route::get('/partidas/{partida}', [PartidaController::class, 'show'])->name('partidas.show');
        Route::get('/partidas/{partida}/estado', [PartidaController::class, 'estado'])->name('partidas.estado');
    });

    // Solo administrador: todas las partidas y gestión de usuarios
    Route::middleware('role:admin')
        ->prefix('admin')->name('admin.')
        ->where(['partida' => '[0-9]+', 'usuario' => '[0-9]+'])
        ->group(function () {
            Route::get('/partidas', [AdminPartidaController::class, 'index'])->name('partidas.index');
            Route::patch('/partidas/{partida}/cancelar', [AdminPartidaController::class, 'cancel'])->name('partidas.cancel');

            Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
            Route::get('/usuarios/crear', [UsuarioController::class, 'create'])->name('usuarios.create');
            Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
            Route::get('/usuarios/{usuario}/editar', [UsuarioController::class, 'edit'])->name('usuarios.edit');
            Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
            Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
        });
});