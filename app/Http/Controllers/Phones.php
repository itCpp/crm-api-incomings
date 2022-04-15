<?php

namespace App\Http\Controllers;

use App\Models\SourceExtensionsName;
use Illuminate\Http\Request;

class Phones extends Controller
{
    /**
     * Метод скрывает номер телефона
     * 
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    public function hidePhone(Request $request)
    {
        /** Разрешенные типы модификации @var */
        $types = [1, 2, 3, 4, 5, 6, 7, 8];
        $type = (int) $request->type;

        if (!in_array($type, $types))
            $request->type = 6;

        if (!$phone = parent::checkPhone($request->phone, $type))
            $phone = $request->phone ?? "0";

        if ($request->extension)
            $phone = $this->getSourceName($request->extension) . $phone;

        return $phone;
    }

    /**
     * Получает наименование источника
     * 
     * @param  string $extension
     * @return string
     */
    public function getSourceName($extension)
    {
        if (!$row = SourceExtensionsName::where('extension', $extension)->first())
            return "";

        return (bool) $row->abbr_name ? $row->abbr_name . " " : "";
    }
}
