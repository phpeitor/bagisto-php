<?php

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Webkul\Culqi\Http\Controllers\ChargeController;
use Webkul\Culqi\Http\Controllers\OrderController;
use Webkul\Culqi\Http\Controllers\WebhookController;

Route::group(['middleware' => ['web']], function () {
    Route::prefix('culqi')->group(function () {
        Route::post('/charge', [ChargeController::class, 'store'])->name('culqi.charge');

        Route::post('/order', [OrderController::class, 'store'])->name('culqi.order.create');

        Route::post('/order/place', [OrderController::class, 'place'])->name('culqi.order.place');
    });
});

Route::post('culqi/webhook', [WebhookController::class, 'store'])
    ->withoutMiddleware(VerifyCsrfToken::class)
    ->name('culqi.webhook');
