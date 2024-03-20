<?php

namespace App\Listeners;

use App\Events\DriverSearchEvent;
use App\Models\Driver;
use App\Models\SearchRide;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use DB;
use Log;

class SendSearchDriver
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
     * @param  \App\Events\DriverSearchEvent  $event
     * @return void
     */
    public function handle(DriverSearchEvent $event)
    {
        $search_ride_id = $event->search_ride_id;
        $distance = $event->distance;

        $search_ride = SearchRide::find($search_ride_id);

        $userLat = $search_ride->pickup_latitude;
        $userLon = $search_ride->pickup_longitude;

        $first_sent_drivers = json_decode($search_ride->first_sent_drivers);
        $second_sent_drivers = json_decode($search_ride->second_sent_drivers);
        $rejected_drivers = json_decode($search_ride->rejected_drivers);

        $exist_drivers = array_merge($first_sent_drivers ?? [],$second_sent_drivers ?? [], $rejected_drivers ?? []);

        $second_sent_autos = Driver::select("drivers.id"
                ,DB::raw("ROUND(6371 * acos(cos(radians(" . $userLat . "))
                * cos(radians(drivers.latitude))
                * cos(radians(drivers.longitude) - radians(" . $userLon . "))
                + sin(radians(" .$userLat. "))
                * sin(radians(drivers.latitude))),1) AS distance"),"drivers.latitude","drivers.longitude","drivers.image")
                ->having('distance', '<=', $distance)
                ->orderBy('distance')->where('current_status',1)->whereNotIn('drivers.id',$exist_drivers)->get();
                //1->online

        return $second_sent_autos;
    }
}
