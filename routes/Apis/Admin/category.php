<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoryController;


Route::controller(CategoryController::class)->prefix('/admin')->middleware('admin')->group(
    function () {

   Route::get('/showAll/category','showAll');
   Route::post('/create/category', 'create');
   Route::get('/edit/category/{id}','edit');
   Route::post('/update/category/{id}', 'update');
   Route::delete('/delete/category/{id}', 'destroy');
   Route::get('/showDeleted/category', 'showDeleted');
Route::get('/restore/category/{id}','restore');
Route::delete('/forceDelete/category/{id}','forceDelete');
   });
