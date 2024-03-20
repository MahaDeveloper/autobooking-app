<?php

namespace App\Listeners;

use App\Events\DriverUserNotificationEvent;
use App\Helpers\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Log;

class UserDriverNotificationSave
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
     * @param  \App\Events\DriverUserNotificationEvent  $event
     * @return void
     */
    public function handle(DriverUserNotificationEvent $event)
    {
        Notification::sendNotification($event->user, $event->title,$event->ride_id, $event->description,$event->type, $event->channel_id);
    }
}
