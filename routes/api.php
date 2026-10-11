<?php

use App\Http\Controllers\Api\V1\PushApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.log', 'api.client'])->prefix('v1')->name('api.v1.')->group(function () {
    Route::post('users', [PushApiController::class, 'register'])->name('register-user');
    Route::put('users/{externalUserId}/token', [PushApiController::class, 'updateToken'])->name('update-token');
    Route::post('pushes', [PushApiController::class, 'send'])->name('send-push');
    Route::get('pushes/{pushNotification}', [PushApiController::class, 'show'])->name('show-push');
});
