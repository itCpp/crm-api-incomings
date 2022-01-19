<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramIncoming;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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
        $data = $request->all();
        $created_at = empty($data['message']['date'])
            ? now() : Carbon::createFromTimestamp($data['message']['date']);

        TelegramIncoming::create([
            'message_id' => $data['message']['message_id'] ?? null,
            'chat_id' => $data['chat']['id'] ?? null,
            'from_id' => $data['from']['id'] ?? null,
            'message' => $data['message']['text'] ?? null,
            'request_data' => $data,
            'created_at' => $created_at,
        ]);

        return response()->json([
            'message' => "Incoming message accepted",
        ]);
    }
}
