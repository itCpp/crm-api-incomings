<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Telegram extends Controller
{
    /**
     * Приём входящих сообщений
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function incoming(Request $request)
    {
        return response()->json([
            'message' => "Incoming message accepted",
        ]);
    }
}
