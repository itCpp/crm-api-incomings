<?php

namespace App\Http\Controllers\Telegram;

use Illuminate\Support\Facades\Http;

trait SendRequest
{
    /**
     * Отправка запроса на сервер
     * 
     * @param string $url
     * @param array $data
     * @return array|mixed
     */
    public function sendRequest($url, $data)
    {
        $response = Http::accept('application/json')
            ->withOptions([
                'verify' => false, // Отключение проверки сетификата
            ])
            ->withHeaders([
                'User-Agent' => $this->getUserAgent(),
                'Accept' => 'application/json',
            ])
            ->post($url, $data);

        return $response->json();
    }
}
