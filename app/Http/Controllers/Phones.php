<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Phones extends Controller
{
    /**
     * Метод скрывает номер телефона
     * 
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    public static function hidePhone(Request $request)
    {
        /** Разрешенные типы модификации @var */
        $types = [1, 2, 3, 4, 5, 6, 7];    

        if (!in_array($request->type, $types))
            $request->type = 6;

        if (!$phone = parent::checkPhone($request->phone, $request->type))
            $phone = $request->phone ?? "0";

        return $phone;
    }
}