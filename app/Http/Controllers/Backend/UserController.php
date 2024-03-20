<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserReward;
use App\Models\Ride;
use App\Models\RideReview;
use App\Models\Driver;
use App\Models\RideBillingDetail;
use App\Models\SearchRide;
use App\Http\Controllers\Controller;
use DB;

class UserController extends Controller
{
    public function userList(Request $request){

        $users = UserResource::collection(User::with('userAddresses','userEmergencyContacts')->when($request->start_date,function($query,$start)
        {
           $query->whereDate('created_at','>=',$start);

        })->when($request->end_date,function($query,$to){

           $query->whereDate('created_at','<=',$to);

        })->get());

        return response()->json(['status' => 'success','users' => $users],200);
    }

    public function userView($id){

        $user = User::with('userAddresses','userEmergencyContacts')->find($id);

        $cancel_rides = Ride::where('user_id',$id)->where('status',2)->count();//cancel

        $missed_rides = SearchRide::where('user_id',$id)->where('status',1)->count();//driver not allocate

        $total_rides = Ride::where('user_id',$id)->count();

        $total_referals = User::where('refferal_id',$id)->count();

        $total_rewards = UserReward::where('user_id',$id)->where('status',0)->sum('reward_amount');

        $total_reward_claimed = UserReward::where('user_id',$id)->where('status',4)->sum('reward_amount');

        $rides = Ride::where('user_id',$id)->pluck('id')->toArray();

        $ratings = RideReview::whereIn('ride_id',$rides)->whereNotNull('user_rating')->sum('user_rating');

        $total_ratings = round($ratings ?? 0 / $total_rides ?? 0);

        $ride_ids = Ride::where('user_id',$id)->pluck('id')->toArray();

        $total_amt_spent = RideBillingDetail::whereIn('ride_id',$ride_ids)->sum('amount');

        return response()->json(['status' => 'success', 'cancel_rides' => $cancel_rides, 'missed_rides' => $missed_rides,'total_rides' => $total_rides, 'total_referals' => $total_referals, 'total_rewards' => $total_rewards, 'total_ratings' => $total_ratings,'total_reward_claimed' => $total_reward_claimed, 'total_amt_spent' => $total_amt_spent, 'user' => $user ], 200);
    }

    public function userStatus($id){

        $user = User::find($id);

        if($user->status == 1){

            $user->status = 0;

            $msg = "The User Deactivated Successfullt";

        }else{

            $user->status = 1;

            $msg = "The User Status Activated Successfully";
        }
        $user->save();

        return response()->json(['status' => 'success','message' => $msg],200);
    }

    public function userRideHistory(Request $request, $user_id){

        $user_ride_history = Ride::with('rideDetail:id,ride_id,pickup_address,drop_address','driver:id,name,image')->when($request->start_date,function($query,$start)
        {
           $query->whereDate('created_at','>=',$start);

        })->when($request->end_date,function($query,$to){

           $query->whereDate('created_at','<=',$to);

        })->where('user_id',$user_id)->get();

        return response()->json(['status' => 'success','user_ride_history' => $user_ride_history],200);
    }

    public function rideHistoryView($ride_id){

        // return now()->subMinutes(4);

        $ride = Ride::with('driver:id,name,image','rideDetail:id,ride_id,pickup_address,drop_address')->with('rideReview', function($q){
            $q->whereNull('driver_rating')->whereNull('driver_review');
        })->find($ride_id);

        return response()->json(['status' => 'success','ride_detail' => $ride],200);
    }

    public function refferals(Request $request){

        $users = User::whereNotNull('refferal_id')->with('userRefferal:id,refferal_id,name,image,created_at')->when($request->start_date,function($query,$start)
        {
           $query->whereDate('created_at','>=',$start);

        })->when($request->end_date,function($query,$to){

           $query->whereDate('created_at','<=',$to);

        })->get();

        $drivers = Driver::whereNotNull('refferal_id')->with('refferer:id,refferal_id,name,image,created_at')->when($request->start_date,function($query,$start)
        {
           $query->whereDate('created_at','>=',$start);

        })->when($request->end_date,function($query,$to){

           $query->whereDate('created_at','<=',$to);

        })->get();

        return response()->json(['status' => 'success','users' => $users,'drivers' => $drivers],200);
    }

    public function userReviews(){

        $user_reviews = Ride::select('id','user_id')->groupBy('user_id')
        ->whereHas('rideReview', function($query) {

            $query->whereNotNull('user_review')->whereNotNull('user_rating');
        })
        ->with(['rideReview' => function($query) {

            $query->whereNotNull('user_review')->whereNotNull('user_rating');

        }])->with('user:id,name,image,mobile')->selectRaw('COUNT(user_id) as total_rides')->get();

        return response()->json(['status' => 'success','user_reviews' => $user_reviews],200);
    }

    public function driverReviews(){

        $driver_reviews = Ride::select('id','driver_id')->groupBy('driver_id')

        ->whereHas('rideReview', function($query) {

            $query->whereNotNull('driver_review')->whereNotNull('driver_rating');
        })
        ->with(['rideReview' => function($query) {

            $query->whereNotNull('driver_review')->whereNotNull('driver_rating');

        }])->with('driver:id,name,image,mobile','driver.driverDetail:id,driver_id,email,qr_code')->selectRaw('COUNT(driver_id) as total_rides')->get();

        return response()->json(['status' => 'success','driver_reviews' => $driver_reviews],200);
    }

}

