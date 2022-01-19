<?php

namespace App\Http\Controllers\Telegram\Commands;

use App\Http\Controllers\Telegram\Telegram;
use Illuminate\Support\Facades\Http;

class HrPaymentReject extends Telegram
{
    /**
     * Текст комманды
     * 
     * @var string
     */
    public $command = "/hr-payment-reject";

    /**
     * Описание команды
     * 
     * @var string
     */
    public $description = "Отклонение запроса на выплату отдела кадров";

    /**
     * Параметры комманды
     * 
     * @var string|int
     */
    protected $chat_id;

    /**
     * Параметры комманды
     * 
     * @var array
     */
    protected $attributes = [];

    /**
     * Создание экземпляра объекта комманды
     * 
     * @param string|int $chat_id
     * @param array $attributes
     * @return void
     */
    public function __construct($chat_id = 0, $attributes = [])
    {
        $this->chat_id = $chat_id;
        $this->attributes = $attributes;
    }

    /**
     * Выполнение команды
     * 
     * @return mixed
     */
    public function handle()
    {
        if (!$this->sendBase()) {

            $id = $this->attributes[0] ?? "б.н.";

            $this->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => "*Ошибка*\r\nРешение по запросу `#{$id}` на выплату не принято, так как сервер обработки сообщений не доступен!\r\n_Для завершения запроса воспользуйтесь сайтом_",
            ]);
        }

        return null;
    }

    /**
     * Отправка запроса на сервер БАЗЫ
     * 
     * @return bool
     */
    public function sendBase()
    {
        $url = env("BASE_API_SERVER", "http://127.0.0.1:8000") . "/api/base/hr/payments/confirm";

        try {
            Http::accept('application/json')
                ->withOptions([
                    'verify' => false, // Отключение проверки сетификата
                ])
                ->withHeaders([
                    'User-Agent' => Telegram::getUserAgent(),
                    'Authorization' => "Telegram Incominget|" . encrypt($this->chat_id),
                    'Accept' => 'application/json',
                ])
                ->post($url, [
                    'id' => $this->attributes[0] ?? null,
                    'confirm' => "reject",
                    'chat_id' => $this->chat_id,
                ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
