<?php

namespace App\Listeners;

use App\Events\UserRegistrationComplete;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class MessageEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserRegistrationComplete  $event
     * @return void
     */
    public function handle(MessageCreatedEvent $event)
    {
        //
    }

    // イベント購読の登録
    public function subscribe($events)
    {
        $events->listen(
                'App\Events\MessageCreatedEvent',
                'App\Listeners\MessageEventListener@onConfirm'
                );
    }

    // イベント発火時の処理
    public function onConfirm($event)
    {
        // 処理
        $messages = \App\Message::get()->toArray();
        //print_r($messages);


        event(new \App\Events\MessageCreatedEvent([1, 4], current($messages)));
    }
}
