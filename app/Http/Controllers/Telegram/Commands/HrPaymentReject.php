<?php

namespace App\Http\Controllers\Telegram\Commands;

use App\Http\Controllers\Telegram\Telegram;

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
        $url = env("BASE_API_SERVER", "http://127.0.0.1:8000") . "/api/base/hr/payments/confirm";
        $data = [
            'id' => $this->attributes[0] ?? null,
            'confirm' => "reject",
            'chat_id' => $this->chat_id,
        ];

        if (!$this->sendBase($url, $data)) {

            $id = $data['id'] ?: "б.н.";

            $this->sendMessage([
                'chat_id' => $this->chat_id,
                'text' => "*Ошибка*\r\nРешение по запросу `#{$id}` на выплату не принято, так как сервер обработки сообщений не доступен!\r\n_Для завершения запроса воспользуйтесь сайтом_",
            ]);
        }

        return null;
    }
}
