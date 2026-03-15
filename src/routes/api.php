<?php


use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\HoldController;
use App\Http\Middleware\CacheStampedeProtection;
use Illuminate\Support\Facades\Route;


Route::prefix('/slots')->group(function () {
    Route::get('/availability', [AvailabilityController::class, 'getAvailability'])
        ->middleware([CacheStampedeProtection::class]);
    Route::post('/{id}/hold', [HoldController::class, 'createHold']);
});
Route::prefix('/holds')->group(function () {
    Route::post('/{id}/confirm', [HoldController::class, 'confirmHold']);
    Route::delete('/{id}', [HoldController::class, 'destroyHold']);
});

