<?php

use App\Http\Controllers\CodecanyonController;
use App\Http\Controllers\ThemeForest\ThemeForestPopularItemsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|   
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('codecanyon/category-popular-items/{id}', [CodecanyonController::class, 'getCategoriesAndPopularItems']);
Route::get('codecanyon/popular-items', [CodecanyonController::class, 'getPopularItems']);
Route::get('codecanyon/failed/popular-items', [CodecanyonController::class, 'getFailedPopularItems']);

Route::get('codecanyon/author/portfolio', [CodecanyonController::class, 'portFolio']);
Route::get('codecanyon/author/portfolio-themeforest', [CodecanyonController::class, 'portFoliothemeforest']);


Route::get('codecanyon/author/add', [CodecanyonController::class, 'addAuthor']);
Route::get('codecanyon/author/add-themeforest', [CodecanyonController::class, 'addAuthorForest']);


//20-1-2025 ******** New updated store FeaturedData and 3 params in existing code by mukesh on 27-1-2025 *****************
Route::get('codecanyon/featured-data', [CodecanyonController::class, 'FeaturedData']);
// ******** 20-1-2025 End *****************


// //28-1-2025 ******** New updated store code and 3 params by mukesh*****************
Route::get('codecanyon/discounted-only', [CodecanyonController::class, 'DiscountedOnly']);
// // ******** 20-1-2025 End *****************


// //29-1-2025 ******** New updated store DiscountedOnly mukesh*****************
Route::get('codecanyon/top-seller', [CodecanyonController::class, 'TopSeller']);
// // ******** 20-1-2025 End *****************

// //29-1-2025 ******** New updated store DiscountedOnly mukesh*****************
Route::get('codecanyon/rising-star', [CodecanyonController::class, 'RisingStar']);
// // ******** 20-1-2025 End *****************

// //29-1-2025 ******** New updated store NewItems mukesh*****************
Route::get('codecanyon/new-items', [CodecanyonController::class, 'NewItems']);
// // ******** 20-1-2025 End *****************

// //29-1-2025 ******** New updated store NewItems mukesh*****************
Route::get('theme/category-popular-items/{id}', [ThemeForestPopularItemsController::class, 'getCategoriesAndPopularItems']);
// // ******** 20-1-2025 End *****************