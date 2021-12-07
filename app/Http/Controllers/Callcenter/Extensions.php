<?php

namespace App\Http\Controllers\Callcenter;

use App\Http\Controllers\Controller;
use App\Models\MkaCrm\CrmUsersToken;
use App\Models\SipInternalExtension;
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
        $empty = md5(microtime());

        if (!$request->number)
            return $empty;

        $first = (string) substr($request->number, 0, 1);

        if ($first === "6")
            $request->number = substr($request->number, 1);

        $session = CrmUsersToken::where('pin', $request->number)->where('deleted_at', null)->first();

        if (!$session)
            return $empty;

        $int = SipInternalExtension::where([
            ['internal_addr', $session->ip],
            ['internal_addr', '!=', null]
        ])->first();

        return $int->extension ?? $empty;
    }
}
