<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ImageController;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('first-blade-example', function(){
    return view('fontend.first-blade-example');
});

Route::get('second-blade-example', function(){
    $comment = 'Tôi là <span class="label label-success">All Laravel</span>'; 
    return view('fontend.second-blade-example')->with('comment', $comment);
});

Route::resource('articles', ArticleController::class);
Route::resource('images', ImageController::class);