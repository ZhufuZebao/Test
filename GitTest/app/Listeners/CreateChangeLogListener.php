<?php

namespace App\Listeners;

use App\Events\EditedByUser;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateChangeLogListener
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
     * @param  EditByUser  $event
     * @return void
     */
    public function handle(EditedByUser $event)
    {
        $model = $event->model;

        $log = new \App\ChangeLog();
        $log->feed($event->model);
        $log->save();

        return;//DEBUG
    }
}
