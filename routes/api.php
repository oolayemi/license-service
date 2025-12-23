<?php

use App\Http\Controllers\Brand\LicenseController as BrandLicenseController;
use App\Http\Controllers\Product\LicenseController as ProductLicenseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('brand')->middleware('auth.brand')->group(function () {
    Route::post('/licenses', [BrandLicenseController::class, 'provision']);
    Route::patch('/licenses/{license}/lifecycle', [BrandLicenseController::class, 'changeLifecycle']);
    Route::get('/licenses', [BrandLicenseController::class, 'listByEmail']);
});

Route::prefix('product')->group(function () {
    Route::post('/license/activate', [ProductLicenseController::class, 'activate']);
    Route::get('/license/status', [ProductLicenseController::class, 'status']);
    Route::post('/license/deactivate', [ProductLicenseController::class, 'deactivateSeat']);
});
