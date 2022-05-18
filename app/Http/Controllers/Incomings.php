<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Call\Asterisk;
use App\Http\Controllers\Call\Mango;
use App\Http\Controllers\Call\RT;
use App\Http\Controllers\Text\IncomingText;
use App\Jobs\IncomingMangoJob;
use App\Jobs\UpdateDurationTime;
use App\Models\Crm\CallDetailRecord as CrmCallDetailRecord;
use App\Models\IncomingEvent;
use App\Models\Old\CallDetailRecords;
use Exception;
use Facade\FlareClient\View;
use FFMpeg\FFMpeg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Incomings extends Controller
{
    /**
     * Входящая текстовая заявка
     * 
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
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
     * Входящие звонки от Ростелекома
     * Параметр `from_number` имеет формат sip:79001234567@0.0.0.0
     * но возможно не всегда...
     * 
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
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
            'session_id' => $request->session_id,
            'user_agent' => $request->header('X-User-Agent') ?: $request->header('User-Agent'),
            'request_data' => parent::encrypt($data),
        ]);

        if ($request->state == "new" and $request->type == "incoming") {
            $hash = md5($event->api_type . $event->session_id . $data['phone'] . $data['sip']);
            RT::event($event, $hash);
        }

        return response()->json([
            'message' => "Запрос обработан",
        ]);
    }

    /**
     * Входящее событие Манго
     * 
     * @param  \Illuminate\Http\Request $request
     * @param  string $type Тип события
     * @return \Illuminate\Http\JsonResponse
     */
    public static function incomingCallEventMango(Request $request, $type)
    {
        $data = $request->all();

        $data['request'] = $data;
        $data['type'] = $type;

        if (is_string($data['json'] ?? null))
            $data['data'] = json_decode($data['json'], true);

        $data['phone'] = $data['data']['from']['number'] ?? null; // Номер звонящего
        $data['sip'] = $data['data']['to']['number'] ?? "0000"; // Номер звонящему

        if (strripos($data['sip'], "@") === false)
            $data['sip'] .= "@mango";

        $event = IncomingEvent::create([
            'api_type' => "Mango",
            'ip' => $request->header('X-Remote-Addr') ?: $request->ip(),
            'session_id' => $data['data']['entry_id'] ?? null,
            'user_agent' => $request->header('X-User-Agent') ?: $request->header('User-Agent'),
            'request_data' => parent::encrypt($data),
        ]);

        if (env("CRM_OLD_WORK")) {
            IncomingMangoJob::dispatch($event, true);
            IncomingMangoJob::dispatch($event)->delay(now()->addMinute());
        } else {
            IncomingMangoJob::dispatch($event);
        }

        return response()->json([
            'message' => "Запрос обработан",
        ]);
    }

    /**
     * Запись любого события
     * 
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public static function events(Request $request)
    {
        $data = $request->all();

        $event = IncomingEvent::create([
            'api_type' => $request->ip(),
            'ip' => $request->header('X-Remote-Addr') ?: $request->ip(),
            'user_agent' => $request->header('X-User-Agent') ?: $request->header('User-Agent'),
            'request_data' => parent::encrypt($data),
        ]);

        return response()->json([
            'message' => "Запрос обработан",
            'data' => $event,
        ]);
    }

    /**
     * Обработка событий внутреннего Asterisk
     * 
     * @param  \Illuminate\Http\Request $request
     * @param  array $params
     * @return \Illuminate\Http\JsonResponse
     */
    public static function asterisk(Request $request, ...$params)
    {
        return Asterisk::asterisk($request, ...$params);
    }

    /**
     * Обновление информации и длине аудиофала
     * 
     * @param  \App\Models\Old\CallDetailRecords $file
     * @return \App\Models\Old\CallDetailRecords
     */
    public static function updateDurationTime(CallDetailRecords $file, $host = null)
    {
        if (!$host = $host ?: env('CALL_RECORDS_SERVER', null))
            return $file;

        $path = $host . $file->path;
        $duration = 0;

        try {
            $duration = self::getDurationFile($path);
            Log::channel('call_duration')->info("Длительность записи: [$duration]", ['id' => $file->id]);
        } catch (Exception $e) {
            Log::channel('call_duration')->error("Длительность не определена: " . $e->getMessage(), ['id' => $file->id]);
        }

        $file->duration = $duration ? round($duration, 0) : null;
        $file->save();

        $url = env('CRM_API_SERVER', 'http://localhost:8000');

        try {
            Http::withHeaders(['Accept' => 'application/json'])
                ->withOptions(['verify' => false])
                ->post($url . "/api/events/callDetailRecord", $file->toArray());
        } catch (Exception $e) {
            Log::channel('call_duration')->error("Информация о длительности не отправлена: " . $e->getMessage(), ['id' => $file->id]);
        }

        return $file;
    }

    /**
     * Подключение и проверка файла
     * 
     * @param  string $path Адрес до файла
     * @return int
     * 
     * @throws \Exception
     */
    public static function getDurationFile($path)
    {
        $ffmpeg = FFMpeg::create();
        $audio = $ffmpeg->open($path);

        return (int) $audio->getFormat()->get('duration');
    }

    /**
     * Просмотр входящих событий
     * 
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Contracts\View\View
     */
    public static function eventView(Request $request, int $id)
    {
        $row = \App\Models\IncomingEvent::find($id);

        if ($row and self::checkIpForDecrypt($request->ip()))
            $row->request_data = parent::decrypt($row->request_data);

        return view('event', [
            'row' => $row ? $row->toArray() : null,
            'next' => $id + 1,
            'back' => $id - 1,
            'id' => $id,
            'ip' => $request->ip(),
        ]);
    }

    /**
     * Проверка IP для вывода расшифровки события
     * 
     * @param  string $ip
     * @return bool
     */
    public static function checkIpForDecrypt(string $ip): bool
    {
        if (in_array($ip, ['91.230.53.106']))
            return true;

        $parts = [
            '192.168.0.',
            '192.168.40.',
            '172.16.255.',
        ];

        foreach ($parts as $part) {
            if (strripos($ip, $part) !== false)
                return true;
        }

        return false;
    }
}
