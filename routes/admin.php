<?php

use Illuminate\Support\Facades\Route;

Route::group(['as' => 'admin.'], function () {
    Route::group(['middleware' => ['auth:admin']], function () {
        // administrators management
        Route::group(['as' => 'administrators.', 'prefix' => 'administrators'], function () {
            Route::post('/valid', ['uses' => 'Admin\AdministratorsController@valid'])->name('valid');
            Route::get('/confirm', ['uses' => 'Admin\AdministratorsController@confirm'])->name('confirm');
        });
        Route::resource('administrators', 'Admin\AdministratorsController');

        // home
        Route::get('/', ['uses' => 'Admin\DashboardController@index'])->name('home');

        // logout
        Route::post('logout', 'Admin\LoginController@logout')->name('logout');
    });

    // login
    Route::get('login', ['uses' => 'Admin\LoginController@login'])->name('login');
    Route::post('login', ['uses' => 'Admin\LoginController@postLogin'])->name('post.login');
});
