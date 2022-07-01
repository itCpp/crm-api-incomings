<?php

namespace App\Http\Controllers\Telegram\Callbacks;

class Handler
{
    /**
     * Список обрабатываемых ботом команд
     * 
     * @var array
     */
    protected $commands = [
        \App\Http\Controllers\Telegram\Commands\Bind::class,
        \App\Http\Controllers\Telegram\Commands\HrPaymentApprove::class,
        \App\Http\Controllers\Telegram\Commands\HrPaymentReject::class,
    ];

    /**
     * Идентификатор телеграма сотрудника
     * 
     * @var string|int
     */
    public $chat_id = null;

    /**
     * Поступившая команда
     * 
     * @var null|object
     */
    public $command = null;

    /**
     * Создание экземпляра объекта
     * 
     * @param string|int $command
     * @param string $command
     * @return void
     */
    public function __construct($chat_id, $command)
    {
        $this->chat_id = $chat_id;
        $this->command = $this->findCommand($command);
    }

    /**
     * Поиск экземпляра объекта команды
     * 
     * @param string $command
     * @return null|object
     */
    public function findCommand($command)
    {
        $data = explode(" ", $command);

        $cmd = $data[0] ?? null;
        unset($data[0]);

        foreach ($data as $row)
            $attributes[] = $row;

        foreach ($this->commands as $class) {

            $object = new $class($this->chat_id, $attributes ?? []);

            if ($object->command == $cmd) {
                return $object;
            }
        }

        return null;
    }
}
