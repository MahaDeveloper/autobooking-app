<?php

namespace App\Http\Controllers\Frontend\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SearchRide;
use App\Models\Driver;
use App\Events\DriverSearchEvent;
use App\Events\DriverUserNotificationEvent;
use App\Events\SearchRideStore;
use Illuminate\Database\Eloquent\Builder;
use DB;
use Log;

class PreBookingController extends Controller
{
    public function firstSearchPrebooking($booking_id){

        $prebooking = SearchRide::find($booking_id);
        $userLat = $prebooking->pickup_latitude;
        $userLon = $prebooking->pickup_longitude;

        $first_sent_autos = Driver::select("drivers.id"
            ,DB::raw("ROUND(6371 * acos(cos(radians(" . $userLat . "))
            * cos(radians(drivers.latitude))
            * cos(radians(drivers.longitude) - radians(" . $userLon . "))
            + sin(radians(" .$userLat. "))
            * sin(radians(drivers.latitude))),1) AS distance"),"drivers.latitude","drivers.longitude","drivers.image")
            ->having('distance', '<=', 1.5)
           ->where('current_status',1)->orderBy('checkin_time')->whereNotNull('checkin_time')->whereDoesntHave('rides', function (Builder $query) {
                $query->where('created_at','<',now());
            })->limit(3)->get(); //1->online

        $first_sent_drivers = $first_sent_autos->pluck('id')->toArray();

        $prebooking->first_sent_drivers = json_encode($first_sent_drivers);
        $prebooking->search_time = now();
        $prebooking->save();

        $driver_ids = json_decode($prebooking->first_sent_drivers);

        if($driver_ids)
       foreach($driver_ids as $driver_id){

        $driver = Driver::find($driver_id);

        $msg = "Hi ".$driver->name.",you Have Reciceived a New Ride Request";

        DriverUserNotificationEvent::dispatch($driver, null, 'AutoKaar', $msg, 'new-request','mrautokaar1');
       }
    }

    public function secondSearchPrebooking($booking_id){

        $prebooking = SearchRide::find($booking_id);

         $distance = 1.5;

        $second_sent_autos = DriverSearchEvent::dispatch($booking_id,$distance);

        $second_sent_drivers = $second_sent_autos[0]->pluck('id')->toArray();

        $prebooking->second_sent_drivers =  json_encode($second_sent_drivers);

        $prebooking->search_time = now();

        $prebooking->save();

        $driver_ids = json_decode($prebooking->second_sent_drivers);

        if($driver_ids){
            foreach($driver_ids as $driver_id){

                $driver = Driver::find($driver_id);

                $title = "AutoKaar";

                $msg = "Hi ".$driver->name.",you Have Reciceived a New Ride Request";

                DriverUserNotificationEvent::dispatch($driver, null, $title, $msg,'new-request','mrautokaar1');
            }
        }
    }

    public function thirdSearchPrebooking($booking_id){

        $prebooking = SearchRide::find($booking_id);

        $distance = 2.5;

        $third_sent_autos = DriverSearchEvent::dispatch($booking_id,$distance);

        $third_sent_drivers = $third_sent_autos[0]->pluck('id')->toArray();

        $prebooking->third_sent_drivers =  json_encode($third_sent_drivers);

        $prebooking->search_time = now();

        $prebooking->save();

        $driver_ids = json_decode($prebooking->third_sent_drivers);

        if($driver_ids){
            foreach($driver_ids as $driver_id){

                $driver = Driver::find($driver_id);

                $title = "AutoKaar";

                $msg = "Hi ".$driver->name.",you Have Reciceived a New Ride Request";

                DriverUserNotificationEvent::dispatch($driver, null, $title, $msg,'new-request','mrautokaar1');
            }
        }
    }
}
