<?php

namespace App\Listeners;

use App\Events\RideDetailSave;
use App\Models\RideDetail;
use App\Models\SearchRide;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Log;
use App\Helpers\MultipleLanguage;

class SaveRideDetail
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
     * @param  \App\Events\RideDetailSave  $event
     * @return void
     */
    public function handle(RideDetailSave $event)
    {
        $request = $event->request;
        $ride_id = $event->ride_id;
        $search_ride_id = $event->search_ride_id;
        $response = $event->response;

        $search_ride = SearchRide::find($search_ride_id);

        if($ride_id)
            $ride_detail = new RideDetail();
            $ride_detail->ride_id = $ride_id;

            if($search_ride){
                $ride_detail->pickup_address = $search_ride->pickup_address;
                $ride_detail->drop_address = $search_ride->drop_address;

                $pickup_address = MultipleLanguage::allLanguages($search_ride->pickup_address);
                $ride_detail->languages_pickup_addresses = json_encode($pickup_address);
        
                $drop_address = MultipleLanguage::allLanguages($search_ride->drop_address);
                $ride_detail->languages_drop_addresses = json_encode($drop_address);

                $ride_detail->final_amount = $search_ride->amount;
            }else{
                $ride_detail->pickup_address = $request->pickup_address;
                $ride_detail->drop_address = $request->drop_address;

                $pickup_address = MultipleLanguage::allLanguages($request->pickup_address);
                $ride_detail->languages_pickup_addresses = json_encode($pickup_address);
        
                $drop_address = MultipleLanguage::allLanguages($request->drop_address);
                $ride_detail->languages_drop_addresses = json_encode($drop_address);

                $ride_detail->final_amount = $response['estimated_ride_fare'];
                $ride_detail->commission_amount = $response['estimated_tax'];
            }
            // $ride_detail->total_amount = $request->total_amount;

            $ride_detail->start_date_time = now();
            // $ride_detail->end_date_time = $request->end_date_time;
            // $ride_detail->emergency_date_time = $request->emergency_date_time;
            $ride_detail->save();

            return $ride_detail;
    }
}
