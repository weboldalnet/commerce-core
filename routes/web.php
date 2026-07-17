<?php

use Weboldalnet\CommerceCore\Http\Controllers\CommercePaymentController;

Route::middleware('web')->group(function () {
    Route::prefix('commerce/payment')
        ->name('commerce.payment.')
        ->group(function () {
            // Payment return URL (online fizetés visszatérése)
            Route::get('/{provider}/return', [CommercePaymentController::class, 'return'])
                ->name('return');

            // Payment callback / webhook URL (provider szerver oldali értesítés)
            Route::post('/{provider}/callback', [CommercePaymentController::class, 'callback'])
                ->name('callback')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });
});
