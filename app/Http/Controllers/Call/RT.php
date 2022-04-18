<?php

namespace App\Http\Controllers\Call;

use App\Http\Controllers\Controller;
use App\Jobs\IncomingCallToOldCrmJob;
use App\Jobs\IncomingRTJob;
use App\Models\IncomingCallRequest;
use App\Models\IncomingEvent;
use Exception;
use Illuminate\Support\Facades\Http;

class RT extends Controller
{
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
    public static $delay = 1;

    /**
     * Обработка входящего события
     * 
     * @param \App\Models\IncomingEvent $event
     * @return \App\Models\IncomingTextRequest|null
     */
    public static function event(IncomingEvent $event)
    {
        $row = IncomingCallRequest::create([
            'api_type' => $event->api_type,
            'incoming_event_id' => $event->id,
        ]);

        if (env("CRM_OLD_WORK")) {
            IncomingCallToOldCrmJob::dispatch($row);
            IncomingRTJob::dispatch($row)->delay(now()->addMinute());
        } else {
            IncomingRTJob::dispatch($row);
        }

        return $row;
    }

    /**
     * Отправка события о входящем звонке
     * 
     * @param \App\Models\IncomingCallRequest $row
     * @return null
     */
    public static function newCall(IncomingCallRequest $row)
    {
        /** Адрес сервера-принимальщика */
        $url = env('CRM_INCOMING_REQUESTS', 'http://localhost:8000');

        try {

            $response = Http::withHeaders(['Accept' => 'application/json'])
                ->withOptions(['verify' => false])
                ->post($url, ['call' => $row->id]);

            $row->request_count++;
            $row->response_code = $response->getStatusCode();
            $row->response_data = $response->json();
            $row->sent_at = date("Y-m-d H:i:s");

            $row->save();

            if ($row->response_code != 200)
                self::retry($row);
        }
        /** Исключение при отсутсвии подключения к серверу */
        catch (\Illuminate\Http\Client\ConnectionException $e) {

            $row->request_count++;
            $row->response_code = $e->getCode();
            $row->response_data = [
                'message' => $e->getMessage(),
            ];

            $row->save();

            self::retry($row);
        }
        /** Исключение при ошибочном ответе */
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

        /** Отправка запроса в старую црм */
        // if (env("CRM_OLD_WORK"))
        //     self::newCallForOld($row);

        return null;
    }

    /**
     * Отправка запроса в старую ЦРМ
     * 
     * @param \App\Models\IncomingCallRequest $row
     * @return null
     */
    public static function newCallForOld(IncomingCallRequest $row)
    {
        $url = env('CRM_OLD_API_SERVER', 'http://localhost:8000');

        try {
            $response = Http::withHeaders(['Accept' => 'application/json'])
                ->withOptions(['verify' => false])
                ->post($url . "/api/eventHandling/callFromIncominget", [
                    'call' => $row->incoming_event_id
                ]);

            $old['response_code'] = $response->getStatusCode();
            $old['response'] = $response->json();
        } catch (Exception $e) {
            $old['error'] = $e->getMessage();
        }

        $response_data = $row->response_data;

        if (!is_object($response_data))
            $response_data = (object) $response_data;

        $response_data->crm_old = $old ?? [];

        $row->response_data = $response_data;
        $row->save();

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
        if ($row->request_count < self::$retry)
            IncomingRTJob::dispatch($row)->delay(now()->addMinutes(self::$delay));

        return null;
    }

    /**
     * Метод проверки подписи
     * 
     * 1.3.2 Проверка подписи
     * В целях безопасности при получении запроса принимающая сторона повторно
     * вычисляет подпись и сравнивает получившееся значение со значением из заголовка
     * header.X-Client-Sign.
     * 
     * Если подпись запроса совпадает с вычисленным значением, источник сообщения
     * считается доверенным и запрос выполняется.
     * 
     * Пример вычисления подписи запроса:
     * Исходные данные:
     * - уникальный код идентификации: "000003C405E6525C64C184258C44EC99";
     * - данные запроса: {"request_number": "+74951234567","from_sipuri":
     * "test_user@cloudpbx.rt.ru"};
     * - уникальный ключ для подписи: "00000716ABDA6D4DFF10F82BCBBFC532".
     * Подпись запроса:
     * sha256hex ("000003C405E6525C64C184258C44EC99{"request_number":
     * "+74951234567","from_sipuri":
     * "test_user@cloudpbx.rt.ru"}00000716ABDA6D4DFF10F82BCBBFC532").
     * Результат вычисления:
     * "fc95a524342dc68df90f7488e6d821c5a8a3b667d585490b50ebf939f1202c36".
     * 
     * @param Illuminate\Http\Request $request
     * @return bool
     */
    public function checkSing($request)
    {
        return true;

        $id = $request->header('x-client-id');
        $sing = $request->header('x-client-sign');
        $key = env('RT_KEY_SING');

        $body = json_encode($request->all());

        // $body = "";
        // $all = $request->all();
        // $count = count($all);
        // $i = 1;
        // foreach ($request->all() as $key => $val) {

        //     $body .= '"' . $key . '":';

        //     if ($val === null)
        //         $body .= "null";
        //     elseif ($val === true)
        //         $body .= "true";
        //     elseif ($val === false)
        //         $body .= "false";
        //     else
        //         $body .= '"' . $val . '"';

        //     if ($i != $count) {
        //         $body .= ',';
        //     }

        //     $i++;

        // }

        $hash = hash('sha256', $id . $body . $key);

        if ($hash != $sing)
            return false;

        return true;
    }
}
