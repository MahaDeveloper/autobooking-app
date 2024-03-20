<?php

namespace App\Http\Controllers\Frontend\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\UserAppValidation;
use App\Models\Support;
use App\Models\user;
use App\Models\Driver;
use App\Models\Ride;
use App\Models\SearchRide;
use App\Models\RideBillingDetail;
use App\Models\UserDriverNotification;
use App\Interfaces\SupportInterface;
use DB;

class UserController extends Controller
{
    public function storeSupport(Request $request,SupportInterface $support){

        $validate = UserAppValidation::supportValidation($request);

        if ($validate['status'] == "error") {
            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        $user = auth()->user();

        $support->supportStore($request);

        return response()->json(['status' => 'success', 'message' => "The Support Has Been Saved"], 200);
    }

    public function emergencyTrigger(){

        $user = auth()->user();

        return response()->json(['status' => 'success', 'emergency_number' => $user->primary_mobile], 200);
    }

    public function userRideList(){

        $user = auth()->user();

        $overall_ride_count = Ride::with('rideDetail')->where('user_id',$user->id)->whereIn('status',[10,12])->count();

        $user_ride_list = Ride::with('driver','rideDetail','rideBillingDetails')->with('rideReview', function($q){
            $q->whereNotNull('driver_rating');
        })->with('driver.driverProofs', function($q){
            $q->where('type',1);

        })->where('user_id',$user->id)->get();

        return response()->json(['status' => 'success', 'overall_ride_count' => $overall_ride_count,'user_ride_list' => $user_ride_list], 200);
    }

    public function rideShow(Request $request){

        $user = auth()->user();

        $ride = Ride::with('driver','driver.driverProofs','rideDetail','rideBillingDetails')->with('rideReview', function($q){
            $q->whereNotNull('driver_rating')->WhereNotNull('driver_review');
        })->find($request->ride_id);

        $total_rides = Ride::where('driver_id',$ride->driver_id)->whereIn('status',[10,12])->count();

        $total_ride_charge = RideBillingDetail::where('ride_id',$request->ride_id)->sum('amount');

        return response()->json(['status' => 'success', 'total_rides' => $total_rides, 'total_ride_charge' => $total_ride_charge, 'ride' => $ride], 200);
        //tax percentage pending
    }

    public function prebookingRideList(){

        $user = auth()->user();

        $prebooking_list = SearchRide::where('status',4)->where('user_id',$user->id)->get();//pre booking

        return response()->json(['status' => 'success', 'prebooking_list' => $prebooking_list], 200);
    }

    public function notificationList(){

        $user = auth()->user();

        $notifications = UserDriverNotification::where('notifiable_id',$user->id)->orderBy('id','DESC')->where('notifiable_type','App\\Models\\User')->get();

        foreach($notifications as $notify){

            $notify->read_status = 1; //read

            $notify->save();
        }

        return response()->json(['status' => 'success','notifications'=> $notifications ],200);
    }

    public function notificationReadStatus(){

        $user = auth()->user();

        $notifications = UserDriverNotification::where('notifiable_id',$user->id)->where('read_status',0)->where('notifiable_type','App\\Models\\User')->count(); //not-view-count

        if($notifications == 0){

            $read_status = 1; //view
        }else{
            $read_status = 0; //not-view
        }

        return response()->json(['status' => 'success','read_status'=> $read_status],200);
    }


}
