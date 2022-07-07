<?php

namespace App\Http\Middleware;

use App\Models\MikrotikCabinetAuth;
use Closure;
use Illuminate\Http\Request;

class AuthInternetCabinet
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $row = MikrotikCabinetAuth::whereToken($request->session()->get('auth_token'))->first();

        if (!$row)
            return view('internet.login');

        return $next($request);
    }
}
