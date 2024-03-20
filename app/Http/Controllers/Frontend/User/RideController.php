<?php

namespace App\Http\Controllers\Frontend\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\UserAppValidation;
use App\Models\Driver;
use App\Models\SearchRide;
use App\Models\Ride;
use App\Models\Zone;
use App\Models\RideDetail;
use App\Models\RideReview;
use App\Models\RideBillingDetail;
use App\Events\SearchRideStore;
use App\Events\RideBillingDetailSave;
use App\Interfaces\UserRideInterface;
use App\Events\RideDetailSave;
use App\Events\SendSmsEvent;
use App\Events\DriverSearchEvent;
use App\Events\DriverPaymentStore;
use DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Log;
use App\Events\AdminNotificationSend;
use App\Events\DriverUserNotificationEvent;

class RideController extends Controller
{
    public function ServiceableAreaCheck(Request $request){

        $validate = UserAppValidation::checkAreaValidation($request);

        if($validate['status'] == "error"){

            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }

        $pickup_zone = Zone::where('pin_code',$request->pickup_pincode)->first();
        $drop_zone = Zone::where('pin_code',$request->drop_pincode)->first();

        if(!$pickup_zone){

            return response()->json(['status' => 'error','message' => 'Your Pickup Location Not In Serviceable Area, Please Try Another Location'],400);
        }
        elseif(!$drop_zone){

            return response()->json(['status' => 'error','message' => 'Your Drop Location Not In Serviceable Area, Please Try Another Location'],400);
        }
        else{
            return response()->json(['status' => 'success','message' => 'You Can Ride Now with Your Selected Location'],200);
        }
    }

    public function bookRide(UserRideInterface $UserRideService,Request $request){

        $validate = UserAppValidation::rideFareValidation($request);

        if($validate['status'] == "error"){

            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $response = $UserRideService->rideFare($request,null);

        return response()->json(['status' => 'success','estimated_ride_fare' => $response['estimated_ride_fare']],200);
    }

    public function userNearbyAutos(Request $request)
    {
        $userLat = $request->current_latitude;
        $userLon = $request->current_longitude;

        $nearby_autos = DB::table("drivers")
        ->select("drivers.id"
            ,DB::raw("ROUND(6371 * acos(cos(radians(" . $userLat . "))
            * cos(radians(drivers.latitude))
            * cos(radians(drivers.longitude) - radians(" . $userLon . "))
            + sin(radians(" .$userLat. "))
            * sin(radians(drivers.latitude))),1) AS distance"),"drivers.latitude","drivers.longitude")
            ->having('distance', '<=', 1.5)->where('current_status',1)
            ->get();

        return response()->json(['status' => 'success', 'nearby_autos' => $nearby_autos],200);
    }

    public function searchFirstSentDriver(UserRideInterface $UserRideService,Request $request)
    {
        $validate = UserAppValidation::searchSentDriverValidation($request);

        if($validate['status'] == "error"){

            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $user = auth()->user();

        $userLat = $request->user_pickup_latitude;
        $userLon = $request->user_pickup_longitude;

        $first_sent_autos = Driver::select("drivers.id"
            ,DB::raw("ROUND(6371 * acos(cos(radians(" . $userLat . "))
            * cos(radians(drivers.latitude))
            * cos(radians(drivers.longitude) - radians(" . $userLon . "))
            + sin(radians(" .$userLat. "))
            * sin(radians(drivers.latitude))),1) AS distance"),"drivers.latitude","drivers.longitude","drivers.image")
            ->having('distance', '<=', 1.5)
            ->orderBy('checkin_time')->where('current_status',1)->whereNotNull('checkin_time')->whereDoesntHave('rides', function (Builder $query) {
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

        return response()->json(['status' => 'success', 'search_ride_id' => $search_ride[0]->id],200);
    }

    public function searchSecondSentDriver(Request $request,$search_ride_id)
    {
        $validate = UserAppValidation::secondSentDriverValidation($request);

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
        if($driver_ids){
            foreach($driver_ids as $driver_id){

                $driver = Driver::find($driver_id);

                $title = "AutoKaar";

                $msg = "Hi ".$driver->name.",you Have Reciceived a New Ride Request";

                DriverUserNotificationEvent::dispatch($driver, null, $title, $msg,'new-request','mrautokaar1');
            }
        }

        if($distance == 2.5 && (!$second_sent_autos)){
            return response()->json(['status' => 'error', 'message' => 'No Auto Available, try After Sometime' ],400);
        }
        return response()->json(['status' => 'success', 'search_ride_id' => $search_ride->id, 'distance' => $distance],200);
    }

    public function driverArrive(Request $request){

        $validate = UserAppValidation::driverArriveValidation($request);

        if($validate['status'] == "error"){

            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }

        $ride = Ride::find($request->ride_id);

        $review = RideReview::where('ride_id',$ride->id)->whereNotNull('driver_rating')->first(['driver_rating']);

        $total_trips = Ride::where('driver_id',$ride->driver_id)->count();

        $driver = Driver::with(['driverProofs' => function($q){

            $q->where('type',1);//vachicle no.

        }])->find($ride->driver_id);

        return response()->json(['status' => 'success', 'otp' => $ride->otp,'total_trips' => $total_trips, 'driver_rating' => $review->driver_rating ?? null, 'ride_duration' => $ride->total_hrs, 'ride_type' => $ride->ride_type,'fare_price' => $ride->final_amount, 'driver' => $driver],200);//pending->total trips count, rating
    }

    public function cancelRide(UserRideInterface $UserRideService,Request $request){
        $validate = UserAppValidation::cancelRideValidation($request);

        if($validate['status'] == "error"){

            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $user = auth()->user();

        $ride = Ride::find($request->ride_id);

        if($request->status == 2){//ride cancelled

            $ride->status = $request->status;

            $ride->save();

            $response = $UserRideService->rideFare($request,$ride);

            $ride_billing = RideBillingDetailSave::dispatch($request,$ride,$response);

            $driver = Driver::find($ride->driver_id);

            $msg = "Hi ".$driver->name.",The Ride Has Been Canceled By User";

            DriverUserNotificationEvent::dispatch($driver, $ride->id, 'AutoKaar', $msg, 'user-cancel-ride',null);

            return response()->json(['status' => 'success', 'message' => 'Ride Has Been Cancelled','ride_billing' => $ride_billing[0]],200);
        }
    }

    public function completedRide(Request $request){

        $validate = UserAppValidation::completedRideValidation($request);

        if($validate['status'] == "error"){

            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $ride = Ride::with('driver.driverProofs','rideDetail','rideBillingDetails')->with('rideReview',function($q){

            $q->whereNotNull('driver_rating');
        })->find($request->ride_id);

        $ride_total_amount = RideBillingDetail::where('ride_id',$ride->id)->sum('amount');

        return response()->json(['status' => 'success', 'ride_details' => $ride, 'ride_total_amount' => $ride_total_amount],200);
    }

    public function addRide(UserRideInterface $UserRideService,Request $request)
    {
        $validate = UserAppValidation::addRideValidation($request);

        if($validate['status'] == "error"){

            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $user = auth()->user();

        $ride = Ride::with('driver.driverProofs','rideDetail','rideBillingDetails')
        ->with('rideReview',function($q){

            $q->whereNotNull('driver_rating');
        })->find($request->ride_id);

        $response = $UserRideService->rideFare($request,$ride);

        $total_trips = Ride::where('driver_id',$ride->driver_id)->whereIn('status',[10,12])->count();

        $ride_billing = RideBillingDetailSave::dispatch($request,$ride,$response);

        $driver = Driver::find($ride->driver_id);

        $msg = "Hi ".$driver->name.",you Have Reciceived a Change Destination Ride Request";

        DriverUserNotificationEvent::dispatch($driver, $ride->id, 'AutoKaar', $msg, 'change-ride-request','mrautokaar');

        return response()->json(['status' => 'success','ride'=>$ride, 'driver_id' => $driver->id, 'total_trips' => $total_trips,'ride_billing' => $ride_billing[0]],200);
    }

    public function addRideConfirm(UserRideInterface $UserRideService,Request $request){

        $validate = UserAppValidation::addRideValidation($request);

        if($validate['status'] == "error"){

            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $user = auth()->user();

        $ride = Ride::with('driver.driverProofs','rideDetail','rideBillingDetails')
        ->with('rideReview',function($q){

            $q->whereNotNull('driver_rating');
        })->find($request->ride_id);

        $ride->status = 7; //change destination request
        $ride->save();

        $response = $UserRideService->rideFare($request,$ride);

        $ride_billing = RideBillingDetailSave::dispatch($request,$ride,$response);

        $driver = Driver::find($ride->driver_id);

        $msg = "Hi ".$driver->name.",you Have Reciceived a New Ride Request";

        DriverUserNotificationEvent::dispatch($driver, $ride->id, 'AutoKaar', $msg, 'add-ride-request','mrautokaar');

        $driver_id = $ride->driver_id;

        return response()->json(['status' => 'success', 'ride'=>$ride, 'driver_id' => $driver_id,  'ride_billing' => $ride_billing[0]],200);
    }

    public function preBooking(UserRideInterface $UserRideService,Request $request){
        $validate = UserAppValidation::preBookingValidation($request);

        if($validate['status'] == "error"){

            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $user = auth()->user();

        $response = $UserRideService->rideFare($request,null);

        if($request->status == 4){//pre booking

            $search_ride = SearchRideStore::dispatch($request,$user->id,null,$response);
        }

        if(env('APP_ENV') != 'localhost'){

            $msg = "Hi ".$user->name.", we have received your ride request. We look forward to fulfill your request ATOKAR.

            Pick-up Date &Time:".$search_ride[0]->prebooking_time."";

            SendSmsEvent::dispatch('1207168059613380532',$user->mobile, $msg);
        }

        return response()->json(['status' => 'success', 'message' => 'Pre Booking has been Save Successfully', 'search_ride' => $search_ride[0]],200);
    }

    public function SearchRideStatus(Request $request){

        $validate = UserAppValidation::searchRideStatusValidation($request);

        if($validate['status'] == "error"){

            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $search_ride = SearchRide::find($request->search_ride_id);

        return response()->json(['status' => 'success', 'search_ride_status' => $search_ride->status],200);
    }

    public function userReviewStore(UserRideInterface $UserRideService,Request $request){

        $validate = UserAppValidation::rideReviewValidation($request);

        if($validate['status'] == "error"){
            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $ride = Ride::find($request->ride_id);

        $response = $UserRideService->rideReview($request,$ride->id);

        return response()->json(['status' => $response['status'], 'message' => 'Review Send Submit Successfully'],200);
    }

    public function cancelSearchRide(Request $request){

        $validate = UserAppValidation::cancelSearchRideValidation($request);

        if($validate['status'] == "error"){
            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $search_ride = SearchRide::find($request->search_ride_id);
        $search_ride->status = 3;
        $search_ride->save();

        return response()->json(['status' => 'success', 'message' => 'Search Ride Canceled'],200);
    }

    public function rideDetail(Request $request){

        $user = auth()->user();

        $validate = UserAppValidation::rideDetailValidation($request);

        if($validate['status'] == "error"){
            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }

        $ride = Ride::with('rideDetail','rideBillingDetails')->with('driver.driverProofs', function ($q){
            $q->where('type',1);//vachicle

        })->with('rideReview', function ($q){
            $q->whereNotNull('driver_rating')->whereNotNull('driver_review');

        })->find($request->ride_id);

        $total_trips = Ride::where('driver_id',$ride->driver_id)->whereIn('status',[10,12])->count();

        $ride_ids = Ride::where('driver_id',$ride->driver_id)->whereIn('status',[10,12])->pluck('id')->toArray();

        $total_count = RideReview::whereIn('ride_id',$ride_ids)->whereNotNull('driver_rating')->count();

        $driver_ratings = RideReview::whereIn('ride_id',$ride_ids)->whereNotNull('driver_rating')->sum('driver_rating');

        if($total_count != 0){
            $total_ratings = round($driver_ratings / $total_count);
        }else{
            $total_ratings = 0;
        }

        return response()->json(['status' => 'success', 'ride' => $ride, 'total_trips' => $total_trips ,'total_ratings' => $total_ratings],200);
    }

    public function trackDriverLocation($driver_id){

        $driver = Driver::find($driver_id);

        return response()->json(['status' => 'success', 'driver' => $driver],200);
    }

    public function addRideCancelContinue(UserRideInterface $UserRideService,Request $request){

        $user = auth()->user();

        $validate = UserAppValidation::addRideCancelContinueValidation($request);

        if($validate['status'] == "error"){
            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }

        $ride = Ride::find($request->ride_id);

        $driver = Driver::find($ride->driver_id);

        if($request->status == 13){ //change trip continue

            $ride->status = $request->status;

            $ride->save();

            $msg = "Hi ".$driver->name.", User Have Choosen to Continue the Current Ride";

            DriverUserNotificationEvent::dispatch($driver, $ride->id, 'AutoKaar', $msg, 'continue-trip','mrautokaar');

            return response()->json(['status' => 'success', 'message' => 'Ride Has Been Continue'],200);


        }else{//14->change trip cancel by user

            $response = $UserRideService->rideFare($request,$ride);

            if($response['total_ride_fare'] != 0){

                $amount = $response['total_ride_fare'];
            }else{
                $amount = $response['estimated_ride_fare'];
            }

            $ride->status = $request->status;
            $ride->final_amount = $amount;

            $ride->save();

            $ride_billing = RideBillingDetailSave::dispatch($request,$ride,$response);

            DriverPaymentStore::dispatch($ride);

            return response()->json(['status' => 'success', 'message' => 'Ride Has Been Cancelled','ride_billing' => $ride_billing[0]],200);
        }
    }

    public function paymentType(Request $request){

        $validate = UserAppValidation::paymentTypeValidation($request);

        if($validate['status'] == "error"){
            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $ride = Ride::find($request->ride_id);
        $ride->payment_type = $request->payment_type;
        $ride->save();

        $driver = Driver::find($ride->driver_id);

        if($request->payment_type == 1){

            $msg = "Hi ".$driver->name.",You Have Reciceived a QR Code Payment Request From User";

        }else{

            $msg = "Hi ".$driver->name.",You Have Reciceived a Manual Cash Payment Request From User";
        }

        DriverUserNotificationEvent::dispatch($driver, $ride->id, 'AutoKaar', $msg, 'payment-type','mrautokaar');

        return response()->json(['status' => 'success', 'message' => 'Payment Status Updated Successfully'],200);
    }

    public function currrentRide(){

        $user = auth()->user();

        $ride = Ride::where('user_id',$user->id)->where('status',3)->latest()->first();//driver on d way

        return response()->json(['status' => 'success', 'current_ride_id' => $ride->id ?? null],200);
    }

}

