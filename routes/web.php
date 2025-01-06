<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    // return view('welcome');
    return redirect()->route('categories.index');
});

Route::resource('categories', CategoryController::class);
Route::get('popular-items', [CategoryController::class, 'showPopularItems'])->name('popular.items');
Route::get('portfolio-items/{author_name?}', [CategoryController::class, 'showPortfolioItems'])->name('portfolio.items');
Route::get('/clear-cache', function() {

     Artisan::call('config:cache');
     Artisan::call('cache:clear');
    return "Success";
});