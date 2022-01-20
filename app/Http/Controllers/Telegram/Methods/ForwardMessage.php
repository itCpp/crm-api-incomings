<?php

namespace App\Http\Controllers\Telegram\Methods;

/**
 * Отправка сообщения от имени бота
 * 
 * @see https://core.telegram.org/bots/api#forwardmessage
 */
class ForwardMessage
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

        $this->url = $this->telegram->url . "/forwardMessage";
    }

    /**
     * Отправка сообщения
     * 
     * @param array $data
     * @return array|mixed
     */
    public function __invoke($data = [])
    {
        return $this->telegram->sendRequest($this->url, $data);
    }
}
