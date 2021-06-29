<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\SomeEvent' => [
            'App\Listeners\EventListener',
        ],
        'App\Events\EditedByUser' => [
            'App\Listeners\CreateChangeLogListener',
        ],
    ];

/*
    // ここにリスナーを登録していく
    protected $subscribe = [
            'App\Listeners\MessageEventListener',
    ];
*/
    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
//echo __FUNCTION__;
        //
//        parent::boot($events);
//        \App\Message::observe(new \App\Observers\MessageObserver);

    }
}
