<?php

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Webkul\Culqi\Http\Controllers\ChargeController;
use Webkul\Culqi\Http\Controllers\WebhookController;

Route::group(['middleware' => ['web']], function () {
    Route::prefix('culqi')->group(function () {
        Route::post('/charge', [ChargeController::class, 'store'])->name('culqi.charge');
    });
});

Route::post('culqi/webhook', [WebhookController::class, 'store'])
    ->withoutMiddleware(VerifyCsrfToken::class)
    ->name('culqi.webhook');
