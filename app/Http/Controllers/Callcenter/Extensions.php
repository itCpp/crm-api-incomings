<?php

namespace App\Http\Controllers\Callcenter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Extensions extends Controller
{
    /**
     * Выдача внутреннего номера оператора колл-центра
     * 
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    public static function getCallerExtension(Request $request)
    {
        return "sar16";
    }
}
