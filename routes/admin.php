<?php

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeviceUserController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\QueueController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.ver')
        ->name('dashboard');

    Route::middleware('permission:clientes.ver')->group(function () {
        Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
    });
    Route::middleware('permission:clientes.crear')->group(function () {
        Route::get('clients/create', [ClientController::class, 'create'])->name('clients.create');
        Route::post('clients', [ClientController::class, 'store'])->name('clients.store');
    });
    Route::middleware('permission:clientes.editar')->group(function () {
        Route::get('clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
        Route::put('clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::post('clients/{client}/api-key', [ClientController::class, 'regenerateKey'])->name('clients.regenerate-key');
    });
    Route::delete('clients/{client}', [ClientController::class, 'destroy'])
        ->middleware('permission:clientes.eliminar')
        ->name('clients.destroy');
    Route::get('clients/{client}/devices', [ClientController::class, 'devices'])->name('clients.devices');

    Route::middleware('permission:dispositivos.ver')->group(function () {
        Route::get('devices', [DeviceUserController::class, 'index'])->name('devices.index');
    });
    Route::middleware('permission:dispositivos.crear')->group(function () {
        Route::get('devices/create', [DeviceUserController::class, 'create'])->name('devices.create');
        Route::post('devices', [DeviceUserController::class, 'store'])->name('devices.store');
    });
    Route::middleware('permission:dispositivos.editar')->group(function () {
        Route::get('devices/{device}/edit', [DeviceUserController::class, 'edit'])->name('devices.edit');
        Route::put('devices/{device}', [DeviceUserController::class, 'update'])->name('devices.update');
    });
    Route::delete('devices/{device}', [DeviceUserController::class, 'destroy'])
        ->middleware('permission:dispositivos.eliminar')
        ->name('devices.destroy');

    Route::middleware('permission:notificaciones.crear')->group(function () {
        Route::get('notifications/create', [NotificationController::class, 'create'])->name('notifications.create');
        Route::post('notifications', [NotificationController::class, 'store'])->name('notifications.store');
    });
    Route::middleware('permission:notificaciones.ver')->group(function () {
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
    });
    Route::middleware('permission:notificaciones.editar')->group(function () {
        Route::get('notifications/{notification}/edit', [NotificationController::class, 'edit'])->name('notifications.edit');
        Route::put('notifications/{notification}', [NotificationController::class, 'update'])->name('notifications.update');
    });
    Route::delete('notifications/{notification}', [NotificationController::class, 'destroy'])
        ->middleware('permission:notificaciones.eliminar')
        ->name('notifications.destroy');

    Route::middleware('permission:cola.ver')->group(function () {
        Route::get('queue', [QueueController::class, 'index'])->name('queue.index');
        Route::get('queue/{notification}', [QueueController::class, 'show'])->name('queue.show');
    });
    Route::post('queue/{notification}/cancel', [QueueController::class, 'cancel'])
        ->middleware('permission:cola.cancelar')
        ->name('queue.cancel');
    Route::post('queue/{notification}/retry', [QueueController::class, 'retry'])
        ->middleware('permission:cola.reintentar')
        ->name('queue.retry');

    Route::middleware('permission:logs.ver')->group(function () {
        Route::get('logs/{type}', [LogController::class, 'index'])
            ->whereIn('type', ['api', 'firebase-requests', 'firebase-logs'])
            ->name('logs.index');
        Route::get('logs/{type}/{log}', [LogController::class, 'show'])
            ->whereIn('type', ['api', 'firebase-requests', 'firebase-logs'])
            ->whereNumber('log')
            ->name('logs.show');
    });

    Route::middleware('permission:usuarios.ver')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
    });
    Route::middleware('permission:usuarios.crear')->group(function () {
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
    });
    Route::middleware('permission:usuarios.editar')->group(function () {
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    });
    Route::delete('users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:usuarios.eliminar')
        ->name('users.destroy');

    Route::middleware('permission:roles.ver')->group(function () {
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    });
    Route::middleware('permission:roles.crear')->group(function () {
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
    });
    Route::middleware('permission:roles.editar')->group(function () {
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    });
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])
        ->middleware('permission:roles.eliminar')
        ->name('roles.destroy');
});
