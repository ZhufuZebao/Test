<?php
namespace App\Observers;

use App\Http\Requests;
use App\Message;
use Request;
use L5Redis;

class MessageObserver {

    public function saving($model)
    {
        //
//echo __FUNCTION__;
    }

    public function saved(Message $model)
    {
//echo __FUNCTION__;
//        $messages = \App\Message::with('fromUser')->with('toUser')
//        ->where('id', $model->id)->get()->toArray();
//        event(new \App\Events\MessageCreatedEvent([$model->from_user_id, $model->to_user_id], current($messages)));

        $messages = \App\Message::get()->toArray();
        /*
//print_r($messages);

        $redis = L5Redis::connection();

        $redis->publish('chat.message', json_encode([
                'users'     => [1, 4],
                'message'   => current($messages),
                'system'   => true,
        ]));
*/
        event(new \App\Events\MessageCreatedEvent([1, 4], current($messages)));
    }

}
