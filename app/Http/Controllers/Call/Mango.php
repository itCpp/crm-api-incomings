<?php

namespace App\Http\Controllers\Call;

use App\Http\Controllers\Controller;
use App\Models\IncomingCallRequest;
use App\Models\IncomingEvent;
use App\Jobs\IncomingMangoJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class Mango extends Controller
{
    /**
     * Ключи манго-офиса
     * 
     * @var array
     */
    protected $keys = [];

    /**
     * Полученная подпись
     * 
     * @var string
     */
    protected $sing = "";

    /**
     * Полученный api ключ
     * 
     * @var string
     */
    protected $vpbx_api_key = "";

    /**
     * Количество попыток отправки события при неудачном ответе
     * 
     * @var int
     */
    public static $retry = 3;

    /**
     * Отсрочка следующей попытки отправки после неудачной
     * 
     * @var int Минуты
     */
    public static $delay = 2;

    /**
     * Создание объекта
     * 
     * @return void
     */
    public function __construct()
    {
        $this->keys = json_decode(env('APP_MANGOKEYS_BALANCE', "[]"));
    }

    /**
     * Метод проверки подписи для предотвращения атак
     * 
     * @param string $json Входящие данные для сверки подписи
     * @return bool
     */
    public function checkSing($json)
    {

        foreach ($this->keys as $row) {

            if ($row->key == $this->vpbx_api_key)
                return hash('sha256', $row->key . $json . $row->salt) == $this->sing;
        }

        return false;
    }

    /** 
     * Обработка входящего события от сервера Манго офиса
     * 
     * @param \App\Models\IncomingEvent $event
     * @return response
     */
    public function event(IncomingEvent $event)
    {
        $request = new Request(query: $this->decrypt($event->request_data));

        $this->sing = $request->sign ?? ""; // Подпись от сервера манго
        $this->vpbx_api_key = $request->vpbx_api_key ?? ""; // Api ключ от сервера манго

        $all = $request->all();
        $json = isset($all['request']['json']) ? json_decode($all['request']['json'], true) : null;

        // if (!$this->checkSing($all['json'] ?? ""))
        //     return null;

        $data = [
            'call_state' => $json['call_state'] ?? "Null", // Тип события
            'phone' => $json['from']['number'] ?? null, // Номер звонящего
            'sip' => $json['to']['number'] ?? null, // Номер звонящему
            'timestamp' => $json['timestamp'] ?? time(), // Время события
        ];

        $method = "event" . $data['call_state']; // Наименование метода обработки события

        if (!method_exists($this, $method))
            return null;

        if (!$row = IncomingCallRequest::where('incoming_event_id', $event->id)->first()) {
            $row = IncomingCallRequest::create([
                'api_type' => $event->api_type,
                'incoming_event_id' => $event->id,
            ]);
        }

        return $this->$method($row);
    }

    /**
     * Метод обработки события начала входящего звонка
     * 
     * @param \App\Models\IncomingCallRequest $row
     * @return null
     */
    public function eventAppeared($row)
    {
        return $this->send($row);
    }

    /**
     * Отправка данных на сервер
     * 
     * @param \App\Models\IncomingCallRequest $row
     * @return null
     */
    public function send($row)
    {

        // Адрес сервера-принимальщика
        $url = env('CRM_INCOMING_REQUESTS', 'http://localhost:8000');

        try {

            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])
                ->withOptions([
                    'verify' => false, // Отключение проверки сетификата
                ])
                ->post($url, [
                    'call' => $row->id,
                ]);

            $row->request_count++;
            $row->response_code = $response->getStatusCode();
            $row->response_data = $response->json();
            $row->sent_at = date("Y-m-d H:i:s");

            $row->save();

            if ($row->response_code != 200)
                self::retry($row);
        }
        // Исключение при отсутсвии подключения к серверу
        catch (\Illuminate\Http\Client\ConnectionException $e) {

            $row->request_count++;
            $row->response_code = $e->getCode();
            $row->response_data = [
                'message' => $e->getMessage(),
            ];

            $row->save();

            self::retry($row);
        }
        // Исключение при ошибочном ответе
        catch (\Illuminate\Http\Client\RequestException $e) {

            $row->request_count++;
            $row->response_code = $e->getCode();
            $row->response_data = [
                'message' => $e->getMessage(),
            ];
            $row->sent_at = date("Y-m-d H:i:s");

            $row->save();

            self::retry($row);
        }

        return null;
    }

    /**
     * Повторная попытка отправки запроса
     * 
     * @param \App\Models\IncomingCallRequest $row
     * @return null
     */
    public static function retry(IncomingCallRequest $row)
    {

        if ($row->request_count < self::$retry) {
            $event = IncomingEvent::find($row->incoming_event_id);
            IncomingMangoJob::dispatch($event)->delay(now()->addMinutes(self::$delay));
        }

        return null;
    }
}
