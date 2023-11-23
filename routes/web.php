<?php

use Illuminate\Support\Facades\Route;

Route::group(['as' => 'web.'], function () {
    // home
    Route::get('/', ['uses' => 'Web\HomeController@index'])->name('home');
});
