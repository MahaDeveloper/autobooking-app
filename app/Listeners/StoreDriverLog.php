<?php

namespace App\Listeners;

use App\Events\DriverLogStore;
use App\Models\DriverLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class StoreDriverLog
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
     * @param  \App\Events\DriverLogStore  $event
     * @return void
     */
    public function handle(DriverLogStore $event)
    {
        $driver_id = $event->driver_id;
        $request = $event->request;

        if($request->current_status == 1){//online

            $driver_log = new DriverLog();
            $driver_log->check_in_time = now();
            $driver_log->driver_id =  $driver_id;
            $driver_log->date =  now();
        }

        if($request->current_status == 2){//offline

            $driver_log = DriverLog::find($request->driver_log_id);

            $driver_log->check_out_time = now();
        }

        $driver_log->save();

        return $driver_log;
    }
}
