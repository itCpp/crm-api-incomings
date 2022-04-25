<?php

use App\Http\Middleware\WriteApiAccessQuery;
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
Route::any('rt', 'Incomings@incomingCallEventRT')->middleware(WriteApiAccessQuery::class);
Route::post('rt/call_events', 'Incomings@incomingCallEventRT')->middleware(WriteApiAccessQuery::class);

/** Входящие события Манго */
Route::any('mango/events/{type}', 'Incomings@incomingCallEventMango')->middleware(WriteApiAccessQuery::class);

/** Входящее события с внутренней звонилки */
Route::any('events', 'Incomings@events');

/** Обработка событий внутреннего Asterisk */
Route::any('asterisk/{a?}/{b?}/{c?}', 'Incomings@asterisk');

/** Определение очереди распределения звонков */
Route::any('getQueueSectorCall', 'Callcenter\SectorQueue@getSector');

/** Вывод внутреннего номера */
Route::get('getCallerExtension', 'Callcenter\Extensions@getCallerExtension');

/** Прием входящих сообщений телеграма */
Route::any('telegram{token}/incoming', 'Telegram\Incoming')->middleware(WriteApiAccessQuery::class);
