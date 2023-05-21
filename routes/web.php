<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () { return view('welcome'); });

Route::resource('articles', ArticleController::class);
Route::resource('images', ImageController::class);

Route::get('excel',function() { return view('fontend.excel'); });
Route::get('export-user', [UserController::class,'exportUser'])->name('export-user');
Route::post('import-user', [UserController::class,'importUser'])->name('import-user');