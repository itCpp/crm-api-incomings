<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SendRequests extends Controller
{
    
    /**
     * Отправка запроса на сервер ЦРМ
     * 
     * @param int $id
     * @return string|object|null
     */
    public static function send($id = null)
    {

        $url = env('CRM_INCOMING_REQUESTS', 'http://localhost:8000');

        $response = Http::withOptions([
            'verify' => false,
        ])
        ->get("{$url}/?id={$id}");

        return $response->json();

    }

}
