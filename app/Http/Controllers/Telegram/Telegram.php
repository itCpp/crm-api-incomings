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

        $create = [];

        // Обратная функция кнопки
        if ($data['callback_query'] ?? null) {
            $create = [
                'message_id' => $data['callback_query']['message']['message_id'] ?? null,
                'chat_id' => $data['callback_query']['message']['chat']['id'] ?? null,
                'from_id' => $data['callback_query']['from']['id'] ?? null,
                'message' => $data['callback_query']['data'] ?? null,
                'is_callback_query' => 1,
            ];
        }
        // Редактирование сообщения
        else if ($data['edited_message'] ?? null) {
            $create = [
                'message_id' => $data['edited_message']['message_id'] ?? null,
                'chat_id' => $data['edited_message']['chat']['id'] ?? null,
                'from_id' => $data['edited_message']['from']['id'] ?? null,
                'message' => $data['edited_message']['text'] ?? null,
                'is_edited_message' => 1,
            ];

            $date = $data['edited_message']['edit_date'] ?? null;
        }
        // Обычное входящее сообщение
        else if ($data['message'] ?? null) {
            $create = [
                'message_id' => $data['message']['message_id'] ?? null,
                'chat_id' => $data['message']['chat']['id'] ?? null,
                'from_id' => $data['message']['from']['id'] ?? null,
                'message' => $data['message']['text'] ?? null,
            ];

            $date = $data['message']['date'] ?? null;
        }
        

        $created_at = !isset($date)
            ? now() : Carbon::createFromTimestamp($date);

        TelegramIncoming::create(array_merge($create, [
            'request_data' => $data,
            'created_at' => $created_at,
        ]));

        return response()->json([
            'message' => "Incoming message accepted",
        ]);
    }
}
