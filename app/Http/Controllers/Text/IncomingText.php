<?php

namespace App\Http\Controllers\Text;

use App\Http\Controllers\Controller;
use App\Jobs\IncomingTextJob;
use App\Models\IncomingEvent;
use App\Models\IncomingTextRequest;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class IncomingText extends Controller
{

    /**
     * Количество попыток отправки события при неудачном ответе
     * 
     * @var int
     */
    public static $retry = 3;
    
    /**
     * Обработка входящего события
     * 
     * @param \App\Models\IncomingEvent $event
     * @return \App\Models\IncomingTextRequest
     */
    public static function event(IncomingEvent $event)
    {

        $row = IncomingTextRequest::create([
            'incoming_event_id' => $event->id,
        ]);

        IncomingTextJob::dispatch($row);

        return $row;

    }

    /**
     * Отправка запроса на внутренний сервер
     * 
     * @param \App\Models\IncomingTextRequest $row
     * @return null
     */
    public static function send(IncomingTextRequest $row)
    {

        // Адрес сервера-принимальщика
        $url = env('CRM_INCOMING_TEXT_REQUESTS', 'http://localhost:8000'); 

        try {

            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])
            ->withOptions([
                'verify' => false, // Отключение проверки сетификата
            ])
            ->post($url, [
                'id' => $row->id,
            ]);

            $row->request_count++;
            $row->response_code = $response->getStatusCode();
            $row->response_data = $response->json();
            $row->sent_at = date("Y-m-d H:i:s");

            $row->save();

            if ($row->response_code != 200)
                IncomingText::retry($row);

        }
        // Исключение при отсутсвии подключения к серверу
        catch (\Illuminate\Http\Client\ConnectionException $e) {

            $row->request_count++;
            $row->response_code = $e->getCode();
            $row->response_data = [
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
                'previos' => $e->getPrevious(),
            ];

            $row->save();

            IncomingText::retry($row);

        }
        // Исключение при ошибочном ответе
        catch (\Illuminate\Http\Client\RequestException $e) {

            $row->request_count++;
            $row->response_code = $e->getCode();
            $row->response_data = [
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
                'previos' => $e->getPrevious(),
            ];
            $row->sent_at = date("Y-m-d H:i:s");

            $row->save();

            IncomingText::retry($row);

        }

        return null;

    }

    /**
     * Повторная попытка отправки запроса
     * 
     * @param \App\Models\IncomingTextRequest $row
     * @return null
     */
    public static function retry(IncomingTextRequest $row)
    {

        if ($row->request_count < self::$retry)
            IncomingTextJob::dispatch($row)->delay(now()->addMinutes(5));

        return null;

    }

}
