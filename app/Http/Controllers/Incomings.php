<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Text\IncomingText;
use App\Models\IncomingEvent;

class Incomings extends Controller
{
    
    /**
     * Входящая текстовая заявка
     * 
     * @param \Illuminate\Http\Request $request
     * @return response
     */
    public static function incomingTextRequest(Request $request)
    {

        $event = IncomingEvent::create([
            'api_type' => "text",
            'ip' => $request->header('X-Remote-Addr') ?: $request->ip(),
            'user_agent' => $request->header('X-User-Agent') ?: $request->header('User-Agent'),
            'request_data' => parent::encrypt($request->all()),
        ]);

        IncomingText::event($event);

        return response()->json([
            'message' => "Запрос обработан",
        ]);

    }

}
