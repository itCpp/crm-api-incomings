<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use App\Models\TelegramOutgoing;
use Exception;
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
        $outgoing = new TelegramOutgoing;
        $outgoing->request_data = Controller::encrypt($data);
        $outgoing->bot_token = Controller::encrypt($this->getToken());

        foreach (debug_backtrace() as $row) {

            $class = $row['class'] ?? null;
            $function = $row['function'] ?? null;

            if ($class == "App\Http\Controllers\Telegram\Telegram" and $function != "sendRequest") {
                $outgoing->method = $function;
                break;
            }
        }

        try {
            $response = Http::timeout(10)
                ->accept('application/json')
                ->withOptions([
                    'verify' => false, // Отключение проверки сетификата
                ])
                ->withHeaders([
                    'User-Agent' => $this->getUserAgent(),
                    'Accept' => 'application/json',
                ])
                ->post($url, $data);

            $json = (array) $response->json();

            $outgoing->response_code = $response->status();
        } catch (Exception $e) {
            $json = [
                'ok' => false,
                'error_code' => $e->getCode(),
                'description' => $e->getMessage(),
            ];
        }

        $outgoing->created_at = now();
        $outgoing->response_data = Controller::encrypt($json ?? []);
        $outgoing->save();

        return $outgoing->response_data;
    }

    /**
     * Отправка запроса на сервер БАЗЫ
     * 
     * @param string $url
     * @param array $data
     * @return bool
     */
    public function sendBase($url, $data)
    {
        try {
            Http::accept('application/json')
                ->withOptions([
                    'verify' => false, // Отключение проверки сетификата
                ])
                ->withHeaders([
                    'User-Agent' => Telegram::getUserAgent(),
                    'Authorization' => "Telegram Incominget|" . encrypt($this->chat_id ?? ""),
                    'Accept' => 'application/json',
                ])
                ->post($url, $data);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
