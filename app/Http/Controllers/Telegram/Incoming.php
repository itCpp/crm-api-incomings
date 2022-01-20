<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Telegram\Jobs\CallbackQueryJob;
use App\Http\Controllers\Telegram\Jobs\ReplyMessagesJob;
use App\Models\TelegramChatIdForward;
use App\Models\TelegramIncoming;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class Incoming extends Controller
{
    /**
     * Приём входящих сообщений
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
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

            CallbackQueryJob::dispatch($create['from_id'] ?? 0, $create['message'] ?? "");
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
        // События в группах
        else if ($data['my_chat_member'] ?? null) {
            $create = [
                'chat_id' => $data['my_chat_member']['chat']['id'] ?? null,
                'from_id' => $data['my_chat_member']['from']['id'] ?? null,
            ];

            $date = $data['my_chat_member']['date'] ?? null;
        }
        // Канал
        else if ($data['channel_post'] ?? null) {
            $create = [
                'chat_id' => $data['channel_post']['chat']['id'] ?? null,
                'from_id' => $data['channel_post']['sender_chat']['id'] ?? null,
                'message_id' => $data['channel_post']['message_id'] ?? null,
                'message' => $data['channel_post']['text'] ?? null,
            ];

            $date = $data['channel_post']['date'] ?? null;
        }

        $created_at = !isset($date)
            ? now() : Carbon::createFromTimestamp($date);

        if (isset($create['message']))
            $create['message'] = $this->encrypt($create['message']);

        $incoming = TelegramIncoming::create(array_merge($create, [
            'request_data' => $this->encrypt($data),
            'created_at' => $created_at,
        ]));

        $forward = TelegramChatIdForward::where('from_chat_id', $incoming->chat_id)
            ->orderBy('from_chat_id')
            ->get()
            ->map(function ($row) use ($incoming) {
                return [
                    'chat_id' => $row->to_chat_id,
                    'from_chat_id' => $incoming->chat_id,
                    'message_id' => $incoming->message_id,
                ];
            })
            ->toArray();

        if (count($forward))
            ReplyMessagesJob::dispatch(...$forward);

        return response()->json([
            'message' => "Incoming message accepted",
        ]);
    }
}
