<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Reports\BestSellerController;
use App\Http\Controllers\Reports\DiscountSaleController;
use App\Http\Controllers\Reports\FeaturedController;
use App\Http\Controllers\Reports\NewestItemController;
use App\Http\Controllers\Reports\RisingStarController;
use App\Http\Controllers\ThemeForest\ThemeForestCategoryController;
use App\Http\Controllers\ThemeForest\ThemeForestPopularitemController;
use App\Models\ThemeForestCategory;

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
Route::get('portfolio-items-themeforest/{author_name?}', [CategoryController::class, 'showPortfolioItemsThemeForest'])->name('portfolio.items.themeforest');

Route::get('popular-items/reports', [CategoryController::class, 'showPopularReports'])->name('popular.items.reports');

//20-1-2025 ******** New *****************
Route::get('theme-forest', [CategoryController::class, 'themeForest']);
Route::get('theme-forest-popular-items', [ThemeForestPopularitemController::class, 'themeForestPopular'])->name('theme.popular');



// ******** 20-1-2025 End *****************
// ******** 29-1-2025 Mukesh *****************
Route::prefix('reports')->group(function () {
    Route::get('/best-sellers', [BestSellerController::class, 'index'])->name('reports.best-sellers');
    Route::get('/discount-sale', [DiscountSaleController::class, 'index'])->name('reports.discount-sale');
    Route::get('/featured', [FeaturedController::class, 'index'])->name('reports.featured');
    Route::get('/newest-item', [NewestItemController::class, 'index'])->name('reports.newest-item');
    Route::get('/rising-star', [RisingStarController::class, 'index'])->name('reports.rising-star');
});

Route::resource('theme-categories', ThemeForestCategoryController::class);

Route::get('/clear-cache', function () {
    Artisan::call('config:cache');
    Artisan::call('cache:clear');
    return "Success";
});
