<?php

namespace App\Listeners;

use App\Events\RideBillingDetailSave;
use App\Models\SearchRide;
use App\Models\RideBillingDetail;
use App\Models\RideDetail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Log;
use App\Helpers\MultipleLanguage;

class SaveRideBillingDetail
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
     * @param  \App\Events\RideBillingDetailSave  $event
     * @return void
     */
    public function handle(RideBillingDetailSave $event)
    {
        $request = $event->request;
        $ride = $event->ride;
        $response = $event->response;

        $ride_billing_detail = new RideBillingDetail();
        $ride_billing_detail->ride_id = $ride->id;
        $ride_billing_detail->pickup_address = $request->pickup_address;
        $ride_billing_detail->drop_address = $request->drop_address;

        $pickup_address = MultipleLanguage::allLanguages($request->pickup_address);
        $ride_billing_detail->languages_pickup_addresses = json_encode($pickup_address);

        $drop_address = MultipleLanguage::allLanguages($request->drop_address);
        $ride_billing_detail->languages_drop_addresses = json_encode($drop_address);

        $ride_billing_detail->pickup_latitude = $request->pickup_latitude;
        $ride_billing_detail->pickup_longitude = $request->pickup_longitude;
        $ride_billing_detail->drop_latitude = $request->drop_latitude;
        $ride_billing_detail->drop_longitude = $request->drop_longitude;

        //calculate distance
        // distance in kilometers
        // $earthRadius = 6371;

        // $latDiff = deg2rad($request->drop_latitude - $request->pickup_latitude);
        // $lngDiff = deg2rad($request->drop_longitude - $request->pickup_longitude);

        // $a = sin($latDiff / 2) * sin($latDiff / 2) + cos(deg2rad($request->pickup_latitude)) * cos(deg2rad($request->drop_latitude)) * sin($lngDiff / 2) * sin($lngDiff / 2);
        // $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        // $distance = $earthRadius * $c;
        // $rounded_distance = round($distance, 1);

        // if($request->distance){
        //     $ride_billing_detail->distance = $request->distance;
        // }else{
        //     $ride_billing_detail->distance = $rounded_distance;
        // }

        $ride_billing_detail->distance = $request->distance;

        $ride_billing_detail->ride_type = $ride->ride_type;

        if($response['total_ride_fare'] != 0){

            $ride_billing_detail->amount = $response['total_ride_fare'];
        }else{
            $ride_billing_detail->amount = $response['estimated_ride_fare'];
        }

        $fare_details = ['base_fare' => $response['base_fare'], 'distance_fare' => $response['distance_fare'], 'peak_charge' => $response['peak_charge'], 'night_charge' => $response['night_charge'], 'tax_percentage' => $response['tax_percentage'], 'estimated_tax' => $response['estimated_tax'], 'estimated_ride_fare' => $response['estimated_ride_fare'], 'waiting_charge' => $response['waiting_charge'] ?? 0, 'final_tax' => $response['final_tax'] ?? 0, 'total_ride_fare' => $response['total_ride_fare'] ?? 0];

        $ride_billing_detail->fare_details = json_encode($fare_details);
        $ride_billing_detail->save();

        return $ride_billing_detail;
    }
}

