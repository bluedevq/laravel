<?php

use Illuminate\Support\Facades\Route;

Route::group(['as' => 'admin.'], function () {
    // home
    Route::get('/', ['uses' => 'Admin\DashboardController@index'])->name('home');
});
