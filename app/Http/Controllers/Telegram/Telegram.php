<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramIncoming;
use Illuminate\Http\Request;

class Telegram extends Controller
{
    /**
     * Приём входящих сообщений
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function incoming(Request $request)
    {
        TelegramIncoming::create([
            'request_data' => $request->all(),
        ]);

        return response()->json([
            'message' => "Incoming message accepted",
        ]);
    }
}
