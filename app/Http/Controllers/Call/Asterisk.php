<?php

namespace App\Http\Controllers\Call;

use App\Jobs\UpdateDurationTime;
use App\Models\IncomingEvent;
use App\Models\SipTimeEvent;
use App\Models\Old\CallDetailRecords;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Asterisk extends Controller
{
    /**
     * Обработка событий внутреннего Asterisk
     * 
     * @param \Illuminate\Http\Request $request
     * @param array $params Параметры ссылки запроса
     * @return \Illuminate\Http\Response
     */
    public static function asterisk(Request $request, ...$params)
    {
        $data = $request->all();
        $data['params'] = $params;

        $event = IncomingEvent::create([
            'api_type' => "Asterisk",
            'ip' => $request->header('X-Remote-Addr') ?: $request->ip(),
            'session_id' => $request->ID,
            'user_agent' => $request->header('X-User-Agent') ?: $request->header('User-Agent'),
            'request_data' => parent::encrypt($data),
        ]);

        $type = $data['Call'] ?? null;

        if ($type == "Hangup") {

            $time = strtotime($data['DateTime'] ?? null);
            $path = $data['Bases'] ?? null;
            $duration = (int) ($data['TimeCall'] ?? 0);
            $direction = $data['Direction'] ?? "out";

            $phone = parent::checkPhone($data['Number'] ?? null, 3);

            // Сохранение информации об аудиофайле записи разговора
            if ($path and $phone) {

                $file = CallDetailRecords::create([
                    'event_id' => $event->id,
                    'phone' => $phone,
                    'extension' => $data['extension'] ?? null,
                    'path' => $path,
                    'call_at' => $time ? date("Y-m-d H:i:s", $time) : now(),
                    'type' => $direction,
                    'duration' => $duration,
                ]);

                if (!$duration)
                    UpdateDurationTime::dispatch($file);
            }
        }

        // Запись временного события
        $tape = SipTimeEvent::create([
            'event_status' => $request->Call,
            'extension' => $request->extension,
            'event_at' => now(),
        ]);

        return Response::json([
            'message' => "Event accepted",
            'event_id' => $event->id,
            'file_id' => $file->id ?? null,
            'tape' => $tape->ip ?? null,
            // 'data' => $data,
            // 'params' => $params,
        ]);
    }

    /**
     * Приём входящего вызова
     * 
     * @param \Illuminate\Http\Request
     * @return null
     */
    public static function asteriskIncomingCallStart(Request $request)
    {
        return null;
    }
}
