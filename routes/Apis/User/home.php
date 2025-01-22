<?php


use Illuminate\Support\Facades\Route;

use App\Http\Controllers\User\HomeController;


Route::controller(HomeController::class)->group(
    function () {
        Route::get('/showAll/product', 'showAllProduct');
        Route::get('/showAll/category', 'showAllCategory');
    });
