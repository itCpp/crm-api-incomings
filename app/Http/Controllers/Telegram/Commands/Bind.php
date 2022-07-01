<?php

namespace App\Http\Controllers\Telegram\Commands;

use App\Http\Controllers\Telegram\Telegram;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Bind
{
    /**
     * Текст комманды
     * 
     * @var string
     */
    public $command = "/bind";

    /**
     * Описание команды
     * 
     * @var string
     */
    public $description = "Привязка идентификатора Телеграм в ЦРМ";

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
        $host = env('CRM_API_SERVER', "http://localhost:8000");
        $url = Str::finish($host, '/') . "api/base/users/telegram/bind";

        try {
            $response = Http::accept('application/json')
                ->withOptions([
                    'verify' => false, // Отключение проверки сетификата
                ])
                ->post($url, [
                    'chat_id' => $this->chat_id,
                    'code' => $this->attributes[0] ?? null,
                ]);

            $json = $response->json();

            $this->sendAnswer($json['message'] ?? ($response->ok() ? "Запрос успешно обработан" : "Сервер не смог обработать запрос"));
        } catch (Exception $e) {
            $this->sendAnswer("Сервер обработки запроса не доступен");
        } finally {
            return null;
        }
    }

    /**
     * Отправка уведомления об ошибке
     * 
     * @param  string $message
     * @return null
     */
    public function sendAnswer($message)
    {
        (new Telegram(env('TELEGRAM_CRM_TOKEN', "TELEGRAM_CRM_TOKEN")))->sendMessage([
            'chat_id' => $this->chat_id,
            'text' => $message,
        ]);

        return null;
    }
}
