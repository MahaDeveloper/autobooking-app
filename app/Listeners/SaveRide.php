<?php

namespace App\Listeners;

use App\Events\RideSave;
use App\Models\Ride;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SaveRide
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
     * @param  \App\Events\RideSave  $event
     * @return void
     */
    public function handle(RideSave $event)
    {
        $request = $event->request;
        $search_ride = $event->search_ride;
        $driver_id = $event->driver_id;

        $ride = new Ride();
        $ride->user_id = $search_ride->user_id;
        $ride->driver_id = $driver_id;
        $ride->driver_log_id = $driver_id;
        $random = rand(1000,9999);
        $ride->otp = $random;
        $ride->pickup_latitude = $search_ride->pickup_latitude;
        $ride->pickup_longitude = $search_ride->pickup_longitude;
        $ride->drop_latitude = $search_ride->drop_latitude;
        $ride->drop_longitude = $search_ride->drop_longitude;
        $ride->final_amount = $search_ride->amount;
        $ride->distance = $search_ride->distance;
        if($search_ride->status == 4){
            $ride->ride_type = 2;//prebooking
        }
        if($search_ride->ride_type == 3){
            $ride->ride_type = 2; //prebooking
        }elseif($search_ride->ride_type == 2){
            $ride->ride_type = 3; //offline booking by admin
        }elseif($search_ride->ride_type == 1){
            $ride->ride_type = 1; //ride now
        }else{
            $ride->ride_type = $request->ride_type;
        }
        // $ride->payment_type = $request->payment_type;
        $ride->avg_speed = $search_ride->avg_speed;
        $ride->total_hrs = $search_ride->total_hrs;
        $ride->save();

        return $ride;
    }
}
