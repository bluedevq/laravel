<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->as('api.')->group(function () {
    // home
    Route::get('/version', ['uses' => 'Api\HomeController@index'])->name('home');
});
