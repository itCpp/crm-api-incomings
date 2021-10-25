<?php

namespace App\Http\Controllers\Call;

use App\Jobs\UpdateDurationTime;
use App\Models\IncomingEvent;
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
     * @return Response
     */
    public static function asterisk(Request $request, ...$params)
    {
        $data = $request->all();
        $data['params'] = $params;

        $event = IncomingEvent::create([
            'api_type' => "Asterisk",
            'ip' => $request->header('X-Remote-Addr') ?: $request->ip(),
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

        return Response::json([
            'message' => "Событие принято",
            'event' => $event,
            'data' => $data,
            'params' => $params,
        ]);
    }
}
