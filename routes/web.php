<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\UserController;


Route::get('/', function () { return view('welcome'); });

Route::resource('articles', ArticleController::class);
Route::resource('images', ImageController::class);

Route::get('users/exportExcel', [UserController::class,'exportExcel'])->name('users.exportExcel');
Route::post('users/importExcel', [UserController::class,'importExcel'])->name('users.importExcel');
Route::resource('users', UserController::class);