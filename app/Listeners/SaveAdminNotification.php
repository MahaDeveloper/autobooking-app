<?php

namespace App\Listeners;

use App\Events\AdminNotificationSend;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\AdminNotification;

class SaveAdminNotification
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
     * @param  \App\Events\AdminNotificationSend $event
     * @return void
     */
    public function handle(AdminNotificationSend$event)
    {
        $admin_n = new AdminNotification();
        $admin_n->ride_id = $event->ride_id;
        $admin_n->title = $event->title;
        $admin_n->description = $event->description;

        $admin_n->save();
    }
}
