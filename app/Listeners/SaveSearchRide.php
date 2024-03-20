<?php

namespace App\Listeners;

use App\Events\SearchRideStore;
use App\Models\SearchRide;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Carbon\Carbon;
use App\Helpers\MultipleLanguage;

class SaveSearchRide
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
     * @param  \App\Events\SearchRideStore  $event
     * @return void
     */
    public function handle(SearchRideStore $event)
    {
        $request = $event->request;
        $user_id = $event->user_id;
        $sent_drivers = $event->sent_drivers;
        $response = $event->response;

        $search_ride = new SearchRide();
        $search_ride->user_id =  $user_id;
        $search_ride->amount = $response['estimated_ride_fare'];
        $search_ride->pickup_address =  $request->pickup_address;
        $search_ride->drop_address =  $request->drop_address;

        $pickup_address = MultipleLanguage::allLanguages($request->pickup_address);
        $search_ride->languages_pickup_addresses = json_encode($pickup_address);

        $drop_address = MultipleLanguage::allLanguages($request->drop_address);
        $search_ride->languages_drop_addresses = json_encode($drop_address);

        $search_ride->pickup_latitude =  $request->user_pickup_latitude;
        $search_ride->pickup_longitude =  $request->user_pickup_longitude;
        $search_ride->drop_latitude =  $request->user_drop_latitude;
        $search_ride->drop_longitude =  $request->user_drop_longitude;
        $search_ride->distance =  $request->distance;
        if($request->status){
            $search_ride->status =  $request->status;
        }
        if($request->prebooking_time){
            $search_ride->prebooking_time =  $request->prebooking_time;
        }
        $search_ride->first_sent_drivers =  json_encode($sent_drivers);
        // $search_ride->avg_speed = $request->avg_speed;
        $search_ride->total_hrs = $request->ride_duration;

        if($request->status == 4){//prebooking
            $search_ride->search_time =  Carbon::parse($request->prebooking_time)->subMinutes(4);
        }else{
            $search_ride->search_time =  Carbon::now();
        }
        if($request->ride_type){
            $search_ride->ride_type = $request->ride_type;
        }
        $search_ride->save();

        return $search_ride;
    }
}
