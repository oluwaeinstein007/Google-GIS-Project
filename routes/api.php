<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleAPIController;

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

Route::get('/getDistancesAndDurations', [GoogleAPIController::class, 'getDistancesAndDurations'])->name('getDistancesAndDurations');
Route::get('/getForecast', [GoogleAPIController::class, 'getForecast'])->name('getForecast');
Route::get('/getDistTimeMatrix', [GoogleAPIController::class, 'getDistTimeMatrix'])->name('getDistTimeMatrix');
Route::get('/getOptimalOrigin', [GoogleAPIController::class, 'getOptimalOrigin'])->name('getOptimalOrigin');
//route for welcome blade
Route::get('/', function () {
    return view('welcome');
});

//route for pollen
Route::get('/pollen', function () {
    return view('PollenMap');
});

