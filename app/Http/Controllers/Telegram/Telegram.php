<?php

namespace App\Http\Controllers\Telegram;

use App\Exceptions\TelegramBotException;
use App\Http\Controllers\Telegram\Methods\SendMessage;
use App\Http\Controllers\Telegram\Methods\ForwardMessage;

class Telegram
{
    use SendRequest;

    public const APP_NAME = "Telegram kBot";
    public const VERSION = "0.1.0";

    /**
     * Ссылка на сервер телеграм
     * 
     * @var string
     */
    public $url = "https://api.telegram.org";

    /**
     * Токен досутпа
     * 
     * @var string
     */
    protected $token;

    /**
     * Создание экземпляра объекта
     * 
     * @return void
     */
    public function __construct()
    {
        if (!$this->token = env('TELEGRAM_API_TOKEN', null))
            throw new TelegramBotException("Токен доступа API Telegram бота не определен в настройках", 1);

        $this->url .= "/bot{$this->token}";
    }

    /**
     * Отправка сообщения
     * 
     * @param array $data
     * @return array
     */
    public function sendMessage($data)
    {
        return (new SendMessage($this))($data);
    }

    /**
     * Пересылка сообщения
     * 
     * @param array $data
     * @return array
     */
    public function forwardMessage($data)
    {
        return (new ForwardMessage($this))($data);
    }

    /**
     * Формирует строку User-Agent
     * 
     * @return string
     */
    public static function getUserAgent()
    {
        return self::APP_NAME . " v" . self::VERSION;
    }

    /**
     * Выводит токен текущего бота
     * 
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
}
