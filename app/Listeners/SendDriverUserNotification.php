<?php

namespace App\Listeners;

use App\Events\DriverUserNotificationEvent;
use App\Models\UserDriverNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Log;

class SendDriverUserNotification
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
        $notification = new UserDriverNotification();
        $notification->notifiable()->associate($event->user);
        if($event->ride_id){
            $notification->ride_id = $event->ride_id;
        }
        $notification->title = $event->title;
        $notification->description = $event->description;
        $notification->save();
    }
}
