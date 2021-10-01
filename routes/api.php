<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/** Обработка текстовой заявки */
Route::any('inText', 'Incomings@incomingTextRequest');

/** Входящие события РТ */
Route::any('rt', 'Incomings@incomingCallEventRT');

/** Входящие события Манго */
Route::any('mango/events/{type}', 'Incomings@incomingCallEventMango');

/** Входящее события с внутренней звонилки */
Route::any('events', 'Incomings@events');
