<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\UserAppValidation;
use App\Helpers\BackendValidation;
use App\Events\SearchRideStore;
use App\Events\DriverSearchEvent;
use App\Events\DriverUserNotificationEvent;
use App\Interfaces\UserRideInterface;
use App\Models\User;
use App\Models\Driver;
use App\Models\DriverProof;
use App\Models\Ride;
use App\Models\SearchRide;
use DB;
use Illuminate\Database\Eloquent\Builder;

class OfflineBookingController extends Controller
{
    public function offlineBooking(UserRideInterface $UserRideService,Request $request){ //prebooking

        $validate = UserAppValidation::preBookingValidation($request);

        if($validate['status'] == "error"){

            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        if($request->user_id){

            $user = User::find($request->user_id);
        }else{
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            $user->fcm_id = $request->fcm_id;
            $user->primary_mobile = $request->primary_mobile;
            $user->save();
        }
        $response = $UserRideService->rideFare($request,null);

        if($request->status == 4){//pre booking

            $search_ride = SearchRideStore::dispatch($request,$user->id,null,$response);
        }

        return response()->json(['status' => 'success', 'message' => 'Offline Booking has been Save Successfully', 'search_ride_id' => $search_ride[0]->id],200);
    }

    public function offlineFirstSearchAuto(UserRideInterface $UserRideService,Request $request){ //ride now

        $validate = BackendValidation::firstSearchDriverValidation($request);

        if($validate['status'] == "error"){

            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        if($request->user_id){

            $user = User::find($request->user_id);
        }else{
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            $user->fcm_id = $request->fcm_id;
            $user->primary_mobile = $request->primary_mobile;
            $user->save();
        }
        $userLat = $request->user_pickup_latitude;
        $userLon = $request->user_pickup_longitude;

        $first_sent_autos = Driver::select("drivers.id"
            ,DB::raw("ROUND(6371 * acos(cos(radians(" . $userLat . "))
            * cos(radians(drivers.latitude))
            * cos(radians(drivers.longitude) - radians(" . $userLon . "))
            + sin(radians(" .$userLat. "))
            * sin(radians(drivers.latitude))),1) AS distance"),"drivers.latitude","drivers.longitude","drivers.image")
            ->having('distance', '<=', 500)
           ->where('current_status',1)->orderBy('checkin_time')->whereNotNull('checkin_time')->whereDoesntHave('rides', function (Builder $query) {
                $query->where('created_at','<',now());
            })->limit(3)->get(); //1->online

        $response = $UserRideService->rideFare($request,null);

        $first_sent_drivers = $first_sent_autos->pluck('id')->toArray();

        $search_ride = SearchRideStore::dispatch($request,$user->id,$first_sent_drivers,$response);

        $driver_ids = json_decode($search_ride[0]->first_sent_drivers);

        foreach($driver_ids as $driver_id){

            $driver = Driver::find($driver_id);

            $msg = "Hi ".$driver->name.",you Have Reciceived a New Ride Request";

            DriverUserNotificationEvent::dispatch($driver, null, 'AutoKaar', $msg, 'new-request','mrautokaar1');
        }

        return response()->json(['status' => 'success', 'search_ride_id' => $search_ride[0]],200);
    }

    public function offlineSecondSearchAuto(Request $request,$search_ride_id)
    {
        $validate = BackendValidation::searchSecondSentDriverValidation($request);

        if($validate['status'] == "error"){

            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $user = auth()->user();

        $distance = $request->distance;

        $second_sent_autos = DriverSearchEvent::dispatch($search_ride_id,$distance);

        $second_sent_drivers = $second_sent_autos[0]->pluck('id')->toArray();

        $search_ride = SearchRide::find($search_ride_id);

        if($distance == 1.5){

            $search_ride->second_sent_drivers =  json_encode($second_sent_drivers);

        }else{

            $search_ride->third_sent_drivers =  json_encode($second_sent_drivers);
        }

        $search_ride->search_time = now();

        $search_ride->save();

        if($distance == 1.5){

            $driver_ids = json_decode($search_ride->second_sent_drivers);

        }else{
            $driver_ids = json_decode($search_ride->third_sent_drivers);
        }
        if($driver_ids)
        foreach($driver_ids as $driver_id){

            $driver = Driver::find($driver_id);

            $msg = "Hi ".$driver->name.",you Have Reciceived a New Ride Request";

            DriverUserNotificationEvent::dispatch($driver, null, 'AutoKaar', $msg, 'new-request','mrautokaar1');
        }

        if($request->distance == 2.5 && (!$second_sent_autos)){

            return response()->json(['status' => 'error', 'message' => 'No Auto Available, try After Sometime' ],400);
        }
        return response()->json(['status' => 'success', 'search_ride_id' => $search_ride->id, 'distance' => $distance ],200);
    }

    public function userList(){

        $users  = User::active()->get(['id','name','image','mobile']);

        return response()->json(['status' => 'success', 'users' => $users],200);
    }

    public function offlineBookingList(Request $request){

        $ride = SearchRide::with('user:id,name,image')->where('ride_type',2)->when($request->start_date,function($query,$start)
        {
           $query->whereDate('created_at','>=',$start);

        })->when($request->end_date,function($query,$to){

           $query->whereDate('created_at','<=',$to);

        })->get();

        return response()->json(['status' => 'success', 'offline_bookings' => $ride],200);
    }

    public function searchRideStatus(Request $request){

        $validate = BackendValidation::searchRideStatusValidation($request);

        if($validate['status'] == "error"){

            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $search_ride = SearchRide::find($request->search_ride_id);

        return response()->json(['status' => 'success', 'search_ride_status' => $search_ride->status],200);
    }

    public function rideFareDetail(UserRideInterface $UserRideService,Request $request){

        $validate = UserAppValidation::rideFareValidation($request);

        if($validate['status'] == "error"){

            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $response = $UserRideService->rideFare($request,null);

        return response()->json(['status' => 'success','estimated_ride_fare' => $response['estimated_ride_fare']],200);
    }

    public function cancelSearchRide(Request $request){

        $validate = BackendValidation::searchRideStatusValidation($request);

        if($validate['status'] == "error"){

            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }

        $search_ride = SearchRide::find($request->search_ride_id);

        $search_ride->status = 3; //ride cancelled

        $search_ride->save();

        return response()->json(['status' => 'success','message' => 'Search Ride Has Been Canceled'],200);
    }
}
