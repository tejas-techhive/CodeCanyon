<?php

use App\Http\Controllers\CodecanyonController;
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

Route::get('codecanyon/author/add', [CodecanyonController::class, 'addAuthor']);

//20-1-2025 ******** New *****************
Route::get('codecanyon/theme-forest', [CodecanyonController::class, 'themeForest']);


// ******** 20-1-2025 End *****************