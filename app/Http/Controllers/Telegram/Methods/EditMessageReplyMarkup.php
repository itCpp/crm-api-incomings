<?php

namespace App\Http\Controllers\Telegram\Methods;

class EditMessageReplyMarkup
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

        $this->url = $this->telegram . "/editMessageReplyMarkup";
    }

    /**
     * Отправка сообщения
     * 
     * @param array $data
     * @return array
     */
    public function __invoke($data = [])
    {
        return $this->telegram->sendRequest($this->url, $data);
    }
}
