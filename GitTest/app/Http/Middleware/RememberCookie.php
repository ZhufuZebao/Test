<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class RememberCookie
{
    public function handle($request, Closure $next)
    {
        $rememberCookieName = Auth::guard()->getRecallerName();
        $rememberCookieValue = Cookie::get($rememberCookieName);

        $response = $next($request);
        if ($rememberCookieName) {
            // 60分 x 24時間 x 7日 = 10080
            $remember = Cookie::make($rememberCookieName, $rememberCookieValue, 10080);
            if (method_exists($response, 'withCookie')) {
                return $response->withCookie($remember);
            }
        }
        return $response;
    }

}