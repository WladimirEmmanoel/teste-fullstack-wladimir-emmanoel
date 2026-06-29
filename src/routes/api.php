<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AffiliateController;

Route::get('/health', [HealthController::class, 'index']);

/*
 * ROTAS DO ORDER
*/
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/orders/{order}', [OrderController::class, 'show'])->whereNumber('order');
Route::get('/orders/metrics', [OrderController::class, 'metrics']);
Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus']);
Route::get('/affiliates/{affiliate}/summary', [AffiliateController::class, 'summary']);