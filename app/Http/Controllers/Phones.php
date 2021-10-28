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
        $types = [1, 2, 3, 4, 5, 6, 7, 8];    
        $type = (int) $request->type;

        if (!in_array($type, $types))
            $request->type = 6;

        if (!$phone = parent::checkPhone($request->phone, $type))
            $phone = $request->phone ?? "0";

        return $phone;
    }
}
