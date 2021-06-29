<?php
/**
 * Created by PhpStorm.
 * User: P0123443
 * Date: 2020/06/01
 * Time: 13:23
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;


class AdminAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ((Auth::guest() || Auth::guard($guard)->check())) {
            return $next($request);
        }
        return redirect('/loginForm');
    }
}
