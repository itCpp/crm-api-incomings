<?php

use Illuminate\Support\Facades\Route;

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

Route::group([
    'prefix' => 'internet',
    // 'middleware' => \App\Http\Middleware\AuthInternetCabinet::class
], function () {

    Route::get('/', 'Mikrotik\Cabinet@main');
    Route::get('/{name}', 'Mikrotik\Cabinet@main');
});

Route::any('/', function () {
    return response()->json([
        'message' => "Welcome my little friend",
    ]);
});

// Route::get('/event/{id}', 'Incomings@eventView');

/** Обработка номера телефона и вывод его скрытой копии */
Route::get('hidePhone', 'Phones@hidePhone');
