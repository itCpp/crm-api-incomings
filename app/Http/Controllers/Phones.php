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
        $types = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        $type = (int) $request->type;

        $extension = $request->extension ? $this->getSourceName($request->extension) : "";

        /** Экстренное переключение на короткий вариант */
        if ($type == 8 and (bool) $extension)
            $type = 9;

        if (!in_array($type, $types))
            $request->type = 6;

        if (!$phone = parent::checkPhone($request->phone, $type))
            $phone = $request->phone ?? "0";

        if ($request->extension)
            $phone .= $extension;

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

        $row->views++;
        $row->save();

        return (bool) $row->abbr_name ? " " . $row->abbr_name : "";
    }
}
