<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Crypt;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Шифрование всех ключей массива
     * 
     * @param array|object $data
     * @return array
     */
    public static function encrypt($data)
    {

        if (!is_array($data) AND !is_object($data))
            return $data;

        $response = [];

        foreach ($data as $key => $row) {

            $response[$key] = (is_array($row) OR is_object($row))
                ? Controller::encrypt($row)
                : Crypt::encryptString($row);

        }

        return $response;

    }

    /**
     * Расшифровка всех ключей массива
     * 
     * @param array|object $data
     * @return array
     */
    public static function decrypt($data)
    {

        if (!is_array($data) AND !is_object($data))
            return $data;

        $response = [];

        foreach ($data as $key => $row) {

            $response[$key] = (is_array($row) OR is_object($row))
                ? Controller::decrypt($row)
                : Crypt::decryptString($row);
                
        }

        return $response;

    }

}
