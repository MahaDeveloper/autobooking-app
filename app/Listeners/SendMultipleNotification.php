<?php

namespace App\Listeners;

use App\Events\PushNotificationEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Helpers\Notification;
use App\Models\Driver;
use App\Models\User;

class SendMultipleNotification
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
     * @param  \App\Events\PushNotificationEvent  $event
     * @return void
     */
    public function handle(PushNotificationEvent $event)
    {
        $request = $event->request;

        if($request->type == "users"){

            $users = User::where('status',1)->whereNotNull('fcm_id')->get();

        }else{

            $users = Driver::where('status',1)->whereNotNull('fcm_id')->get();  //drivers

        }

        foreach($users as $user){

            Notification::sendNotification($user,$request->title,null,$request->description);
        }
    }
}
