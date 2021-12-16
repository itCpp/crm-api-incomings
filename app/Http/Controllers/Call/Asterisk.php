<?php

namespace App\Http\Controllers\Call;

use App\Jobs\AsteriskIncomingCallStartJob;
use App\Jobs\UpdateDurationTime;
use App\Models\IncomingEvent;
use App\Models\SipTimeEvent;
use App\Models\SipExternalExtension;
use App\Models\SipInternalExtension;
use App\Models\Old\CallDetailRecords;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

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
        $data['channel_extension'] = self::parseChannel($data['channel'] ?? "");
        $data['params'] = $params;

        $event = IncomingEvent::create([
            'api_type' => "Asterisk",
            'ip' => $request->header('X-Remote-Addr') ?: $request->ip(),
            'session_id' => self::createCallId($request->ID, $request->extension),
            'user_agent' => $request->header('X-User-Agent') ?: $request->header('User-Agent'),
            'request_data' => parent::encrypt($data),
        ]);

        $type = $data['Call'] ?? null;
        $direction = $data['Direction'] ?? "out";

        // Начало звонка
        if ($type == "Start" and $direction == "in") {
            AsteriskIncomingCallStartJob::dispatch($event->id);
        }

        if ($type == "Hangup") {

            $time = strtotime($data['DateTime'] ?? null);
            $path = $data['Bases'] ?? null;
            $duration = (int) ($data['TimeCall'] ?? 0);

            $phone = parent::checkPhone($data['Number'] ?? null, 3);
            $phone = $phone ?: $data['Number'];

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
            'extension' => $data['channel_extension'] ?? $request->extension,
            'event_at' => now(),
        ]);

        return response()->json([
            'message' => "Event accepted",
            'event_id' => $event->id,
            'file_id' => $file->id ?? null,
            'tape' => $tape->ip ?? null,
            // 'data' => $data,
            // 'params' => $params,
        ]);
    }

    /**
     * Формирование уникального id
     * 
     * @param string $id
     * @param string|null $extension
     * @return string
     */
    public static function createCallId($id, $extension = null)
    {
        $id = $id ?: microtime(1);

        $parts = explode(".", $id);
        $hash = "";

        foreach ($parts as $part) {
            $hash .= md5($part);
        }
        $hash .= md5($hash);
        $hash .= md5($hash);

        $uuid = "";

        $uuid .= substr($hash, 0, 8);
        $uuid .= "-" . substr($hash, 7, 4);
        $uuid .= "-4" . substr($hash, 11, 3);
        $uuid .= "-8" . substr($hash, 15, 3);
        $uuid .= "-" . substr($hash, 19, 12);

        return $uuid;

        // $call_id = substr(md5($extension), 0, 7);
        // $call_id .= "-" . substr(md5($id), 0, 20);

        // return $call_id;
    }

    /**
     * Метод преобразует строку информации о канале в sip extension
     *          Пример `SIP/sar13-0000a907` в `sar13`
     * 
     * @param string
     * @return string
     */
    public static function parseChannel($channel)
    {
        $explode = explode("-", $channel);
        $sip = $explode[0] ?? "";
        
        return str_replace("SIP/", "", $sip);
    }

    /**
     * Приём входящего вызова для автоматического назначения оператора на заявку
     * 
     * @param int $id
     * @return null
     */
    public static function autoSetPinForRequest($id)
    {
        self::autoSetPinForRequestOldCrm($id);
        self::autoSetPinForRequestNewCrm($id);

        return null;
    }

    /**
     * Назначение оператора на заявку в старой CRM
     *
     * @param int $id
     * @return null
     */
    public static function autoSetPinForRequestOldCrm($id)
    {
        $url = env('CRM_INCOMING_CALL', 'http://localhost:8000');

        try {

            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])
                ->withOptions([
                    'verify' => false, // Отключение проверки сетификата
                ])
                ->post($url, [
                    'call_id' => $id,
                ]);

            if ($response->getStatusCode() != 200)
                self::retryAutoSetPinForRequestOldCrm($id);
        }
        // Исключение при отсутсвии подключения к серверу
        catch (\Illuminate\Http\Client\ConnectionException) {
            self::retryAutoSetPinForRequestOldCrm($id);
        }
        // Исключение при ошибочном ответе
        catch (\Illuminate\Http\Client\RequestException) {
            self::retryAutoSetPinForRequestOldCrm($id);
        }

        return null;
    }

    /**
     * Повторная отправка события
     * 
     * @param int $id
     * @return null
     */
    public static function retryAutoSetPinForRequestOldCrm($id)
    {
        return null;
    }

    /**
     * Отправка события в новую црм
     * 
     * @param int $id Идентификатор события
     * @return null
     */
    public static function autoSetPinForRequestNewCrm($id)
    {
        $url = env('CRM_INCOMING_REQUESTS', 'http://localhost:8000/api') . "/call_asterisk";

        try {

            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])
                ->withOptions(['verify' => false])
                ->post($url, ['call_id' => $id]);

            // if ($response->getStatusCode() != 200)
            //     self::retryAutoSetPinForRequestOldCrm($id);
        }
        // Исключение при отсутсвии подключения к серверу
        catch (\Illuminate\Http\Client\ConnectionException) {
            // self::retryAutoSetPinForRequestOldCrm($id);
        }
        // Исключение при ошибочном ответе
        catch (\Illuminate\Http\Client\RequestException) {
            // self::retryAutoSetPinForRequestOldCrm($id);
        }

        return null;
    }
}
