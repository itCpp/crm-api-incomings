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

Route::any('/', function () {
    return response()->json([
        'message' => "Welcome my little friend",
    ]);
});

Route::get('/s{s}', function ($s) {
    $row = \App\Models\IncomingEvent::find($s);
    return response()->json(\App\Http\Controllers\Controller::decrypt($row->request_data));
});

/** Обработка номера телефона и вывод его скрытой копии */
Route::get('hidePhone', 'Phones@hidePhone');
