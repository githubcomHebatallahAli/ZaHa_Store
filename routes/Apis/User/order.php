<?php


use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\OrderController;


Route::controller(OrderController::class)->group(
    function () {
        Route::post('/create/order', 'create');
    });
