<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Call\RT;
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

    /**
     * Входящие звонки от рос-телекома
     * Параметр `from_number` имеет формат sip:79001234567@0.0.0.0
     * но возможно не всегда...
     * 
     * @param \Illuminate\Http\Request $request
     * @return response
     */
    public static function incomingCallEventRT(Request $request)
    {

        $data = $request->all();

        preg_match('/sip\:(.*?)\@/i', $request->from_number, $matches);
        $data['phone'] = $matches[1] ?? $request->from_number;

        preg_match('/sip\:(.*?)\@/i', $request->request_number, $matches);
        $data['sip'] = isset($matches[1]) ? "sip:" . $matches[1] : $request->request_number;

        $event = IncomingEvent::create([
            'api_type' => "RT",
            'ip' => $request->header('X-Remote-Addr') ?: $request->ip(),
            'user_agent' => $request->header('X-User-Agent') ?: $request->header('User-Agent'),
            'request_data' => parent::encrypt($request->all()),
        ]);

        if ($request->state == "new" AND $request->type == "incoming")
            RT::event($event);

        return response()->json([
            'message' => "Запрос обработан",
        ]);

    }

}
