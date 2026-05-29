<?php

use App\Helpers\CustomPageHelper;

use App\Http\Controllers\Admin\Article\ArticleController;

Route::namespace('App\Http\Controllers\Site')->domain(getSiteDomain())->middleware('web', 'site_share')->group(function () {
    // Site route-ok helye

});

Route::namespace('App\Http\Controllers\Admin')->domain(getAdminDomain())->middleware('web', 'admin_share')->group(function () {

    Route::middleware('auth:admin')->group(function () {
        // Admin route-ok helye
        // Admin ha tovább vannak mappázva
        Route::namespace('Article')->group(function () {

        });
    });
});
