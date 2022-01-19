<?php

namespace App\Http\Controllers\Telegram\Methods;

/**
 * Отправка сообщения от имени бота
 * 
 * @see https://core.telegram.org/bots/api#sendmessage
 */
class SendMessage
{
    /**
     * Ссылка на метод
     * 
     * @var string
     */
    protected $url;

    /**
     * Создание экземпляра объекта
     * 
     * @param \App\Http\Controllers\Telegram\Telegram $telegram
     */
    public function __construct($telegram)
    {
        $this->telegram = $telegram;

        $this->url = $this->telegram->url . "/sendMessage";
    }

    /**
     * Отправка сообщения
     * 
     * @param array $data
     * @return array|mixed
     */
    public function __invoke($data = [])
    {
        $data['parse_mode'] =  "Markdown";

        return $this->telegram->sendRequest($this->url, $data);
    }
}
