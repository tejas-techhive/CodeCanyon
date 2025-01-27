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
Route::get('popular-items/reports', [CategoryController::class, 'showPopularReports'])->name('popular.items.reports');

//20-1-2025 ******** New *****************
Route::get('theme-forest', [CategoryController::class, 'themeForest']);


// ******** 20-1-2025 End *****************

Route::get('/clear-cache', function () {
    Artisan::call('config:cache');
    Artisan::call('cache:clear');
    return "Success";
});
