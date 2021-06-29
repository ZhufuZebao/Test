<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Routing\UrlGenerator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(UrlGenerator $url)
    {
        // 仕様変更 #1369 HTTPS対応
        // $url->forceScheme('https');
        // 2019/05/09
        // SQLをログに記録
        if (config('app.debug')) {
            DB::listen(function ($query) {
                $tmp = str_replace('?', '"' . '%s' . '"', $query->sql);
                $qBindings = [];
                foreach ($query->bindings as $key => $value) {
                    if (is_numeric($key)) {
                        $qBindings[] = $value;
                    } else {
                        $tmp = str_replace(':' . $key, '"' . $value . '"', $tmp);
                    }
                }
                $tmp = vsprintf($tmp, $qBindings);
                $tmp = str_replace("\\", "", $tmp);
                Log::info(' execution time: ' . $query->time . 'ms; ' . trim($tmp));
            });
        }

        // 2017/11/17
        // 標準charasetがutf8mb4となったことで1文字あたりの最大byte数が4bytesに増えた
        // PRIMARY_KEYおよびUNIQUE_KEYを付けたカラムには最大767bytesまでしか入らない
        Schema::defaultStringLength(191);

        /* resourse/views 以下所定のファイルが呼ばれたら変数を代入する */
        \View::composer('/contractor/edit', function ($view) {
            $prefs = [
                0 => "選択してください",
            ];
            foreach(\App\Pref::all() as $pref)
            {
                $prefs[$pref->id] = $pref->name;
            }

            $view->with('prefs', $prefs);
        });
        \View::composer('/job/dashboard', function ($view) {
            $query  = new \App\JobOfferMessageSearchModel();
	    $query->filter = 'inbox';
            $view->with('job_mesgs', $query->search()->paginate(5));
        });
        \View::composer('/job/edit', function ($view) {
            $view->with('contractors',
                        \App\Contractor::select()
                                       ->get()
                                       ->pluck('name','id')
            );
            $view->with('jobs',
                        \App\JobVacancy::select()
                                       ->where('user_id',Auth::id())
                                       ->orderBy('id', 'desc')
                                       ->take(10)
                                       ->pluck('name','id')
            );
            $view->with('skills', \App\Skill::select()
                                            ->get()
                                            ->pluck('name','id')
            );
        });
        \View::composer('/job-message/index', function($view) {
            $userId = Auth::id();
            $unread = \App\JobOfferMessage::whereNull('read_at')->where('sender_id','<>',$userId)->count();

            $view->with('unread', $unread);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
