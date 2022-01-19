<?php

namespace App\Http\Controllers\Telegram\Commands;

class CommandExample
{
    /**
     * Текст комманды
     * 
     * @var string
     */
    public $command = "/CommandExample";

    /**
     * Описание команды
     * 
     * @var string
     */
    public $description = "Пример команды";

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
        return null;
    }
}
