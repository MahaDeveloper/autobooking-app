<?php

namespace App\Listeners;

use App\Events\PushNotificationEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Log;
use App\Models\Notification;
use Illuminate\Support\Facades\Storage;

class SendNotification
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

       $notification = new Notification();
       $notification->title = $request->title;
       $notification->description = $request->description;
       if($request->hasFile('image')){
            $name = Storage::disk('digitalocean')->putFile('notify_images',$request->image,'public');
            $notification->image = $name;
       }
       $notification->type = $request->type;

       $notification->save();
    }
}
