<?php

namespace App\Http\Middleware;

use App\Models\ChatGroup;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CertificationPicture
{
    /**
     * 機関認証
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $url = $request->url();
            $type = explode('/', $url, -1)[5];
            if (is_numeric($type)) {
                $res = ChatGroup::where('group_id', $type)->where('user_id', Auth::id())->get('id')->toArray();
                if (count($res)) {
                    return $next($request);
                } else {
                    return $this->error('');
                }
            } else {
                return $next($request);
            }
        } catch (\Exception $e) {
            return $this->error($e);
        }
    }

    private function error($e)
    {
        Log::error($e);
        return redirect('/#/');
    }
}
