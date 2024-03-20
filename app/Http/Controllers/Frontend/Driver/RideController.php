<?php

namespace App\Http\Controllers\Frontend\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Driver;
use App\Models\Ride;
use App\Models\User;
use App\Models\SearchRide;
use App\Models\OtherCharge;
use App\Models\RideDetail;
use App\Models\RideBillingDetail;
use App\Models\DriverPayment;
use App\Events\DriverLogStore;
use App\Events\RideDetailSave;
use App\Events\RideSave;
use App\Events\RideBillingDetailSave;
use App\Events\DriverPaymentStore;
use App\Events\GiftReward;
use App\Events\SendSmsEvent;
use App\Events\DriverUserNotificationEvent;
use App\Helpers\DriverAppValidation;
use App\Interfaces\UserRideInterface;
use Log;
use App\Events\AdminNotificationSend;

class RideController extends Controller
{
    public function turnOnline(Request $request){

        $validate = DriverAppValidation::turnOnlineValidation($request);

        if($validate['status'] == "error"){
            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }

        $driver  = auth()->user();

        if($request->current_status == 1){//online

            if($driver->verification_status != 2){//proof accept

                return response()->json(['status' => 'error','message' => 'Your Proof Documents Not Verified'],400);
            }
            if($driver->current_status == 4){

                return response()->json(['status' => 'error','message' => 'Your Payment Of Tax is Pending!'],400);

            }elseif($driver->current_status == 5){

                return response()->json(['status' => 'error','message' => 'Your Subscription Date Has Beed Expired!'],400);

            }elseif($driver->current_status == 6){

                return response()->json(['status' => 'error','message' => 'Your Online Permission Has Been Restricted'],400);
            }else{

                $driver->current_status = $request->current_status;
                $driver->checkin_time = Carbon::now();
                $driver->save();

                $driver_log = DriverLogStore::dispatch($driver->id,$request);

                return response()->json(['status' => 'success','message' => 'Online Has Been Activated', 'driver_log_id' => $driver_log[0]->id ?? null],200);
            }
        }else{ //offline
            $driver->current_status = $request->current_status;
            $driver->save();

            DriverLogStore::dispatch($driver->id,$request);

            return response()->json(['status' => 'success','message' => 'You Are In Offline'],200);
        }
    }

    public function updateCurrentLocation(Request $request){

        $validate = DriverAppValidation::updateCurrentLocationValidation($request);

        if($validate['status'] == "error"){
            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }

        $driver = auth()->user();
        $driver->latitude = $request->latitude;
        $driver->longitude = $request->longitude;
        $driver->heading = $request->heading; //travel direction
        $driver->save();

        return response()->json(['status' => 'success','message' => 'Location Has Been Updated'],200);
    }

    public function userRequest(){

        $driver = auth()->user();

        $user_requests = SearchRide::whereIn('status',[0,4])
        ->where(function ($query) use ($driver) {
            $query->whereJsonContains('first_sent_drivers',$driver->id)->orWhereJsonContains('second_sent_drivers',$driver->id)->orWhereJsonContains('third_sent_drivers',$driver->id);
        })->with('user:id,name,image')->get();

        foreach($user_requests as $user){

            $earth_radius = 6371; // in kilometers

            $lat_diff = deg2rad($driver->latitude - $user->pickup_latitude);
            $lng_diff = deg2rad($driver->longitude - $user->pickup_longitude);
            $a = sin($lat_diff / 2) * sin($lat_diff / 2) +
                cos(deg2rad($user->pickup_latitude)) * cos(deg2rad($driver->latitude)) *
                sin($lng_diff / 2) * sin($lng_diff / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $distance = $earth_radius * $c;

            $distance = round($distance, 2);

            // Append the distance in search ride
            $user->customer_distance = $distance;
        }

        return response()->json(['status' => 'success','user_requests' => $user_requests],200);
    }

    public function rideAcceptReject(Request $request)
    {
        $validate = DriverAppValidation::rideAcceptRejectValidation($request);

        if($validate['status'] == "error"){
            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $driver = auth()->user();

        $search_ride = SearchRide::find($request->search_ride_id);

        if($search_ride->status == 2){ //already accept or not

            return response()->json(['status' => 'error','message' => 'This Ride Has Been Accepted By Another Driver'],400);

        }else{

            if($request->ride_status == 2){//driver allocated

                $search_ride->status = $request->ride_status;
                $search_ride->save();

                $ride = RideSave::dispatch($request,$search_ride,$driver->id);

                RideDetailSave::dispatch($request,$ride[0]->id,$search_ride->id,null);

                $user = User::find($ride[0]->user_id);

                $driver = Driver::find($ride[0]->driver_id);

                $title = "AutoKaar";

                if($ride[0]->ride_type == 2){//prebooking

                    $mesg = "Dear ".$user->name.", Driver ".$ride[0]->driver->name." will arrive shortly to your pick-up location.";

                    DriverUserNotificationEvent::dispatch($user, $ride[0]->id, $title, $mesg,'prebook-ride-accept','mrautokaar');
                }else{

                    $mesg = "Dear ".$user->name.", Driver ".$ride[0]->driver->name." will arrive shortly to your pick-up location.";

                    DriverUserNotificationEvent::dispatch($user, $ride[0]->id, $title, $mesg,null,'mrautokaar');

                    $message = "Hi ".$driver->name.", thank you for accepting the ride request. Drive Safely!

                    Please collect the OTP from the passenger and enter it to start your ride.";

                    DriverUserNotificationEvent::dispatch($driver, $ride[0]->id, $title, $message,null,'mrautokaar');
                }

                $admin_notify = "The Ride #  has been accepted by" .$driver->name. " .";

                AdminNotificationSend::dispatch("Ride #",$admin_notify,$ride[0]->id);

                if(env('APP_ENV') != 'localhost'){

                    $msg ="Hi ".$ride[0]->user->name.", your ride request was accepted by the ATOKAR driver. To start your ride, please share your OTP ".$ride[0]->otp.".

                    Note: Share the OTP only when starting your ride.

                    You can share your ride details with your friends or family members. Tap to Share ride details to SOS contact.";

                    SendSmsEvent::dispatch('1207168121911398159',$ride[0]->user->mobile, $msg);
                }

                return response()->json(['status' => 'success','message' => 'Ride Has Been Accepted', 'ride_id'=> $ride],200);//

            }else{//3->reject

                $search_ride->status = $request->ride_status;

                $rejectedDrivers = $search_ride->pluck('rejected_drivers')->first();

                $rejectedDriversArray = json_decode($rejectedDrivers, true) ?? [];

                if (!in_array($driver->id, $rejectedDriversArray)) {

                    array_push($rejectedDriversArray, $driver->id);
                }

                $rejectedDriversJson = $rejectedDriversArray;

                $search_ride->rejected_drivers = json_encode($rejectedDriversJson);

                $search_ride->save();

                return response()->json(['status' => 'success','message' => 'Ride Has Been Rejected'],200);
            }

        }
    }

    public function userPickup(Request $request){

        $validate = DriverAppValidation::userPickupDetailValidation($request);

        if($validate['status'] == "error"){
            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }

        $driver = auth()->user();

        $ride = Ride::with('user:id,name,image,mobile','rideDetail:id,ride_id,pickup_address,drop_address')->find($request->ride_id);

        $ride->status = 3; //driver on the way
        $ride->save();

        $earth_radius = 6371; // in kilometers

        $lat_diff = deg2rad($driver->latitude - $ride->pickup_latitude);
        $lng_diff = deg2rad($driver->longitude - $ride->pickup_longitude);
        $a = sin($lat_diff / 2) * sin($lat_diff / 2) +
            cos(deg2rad($ride->pickup_latitude)) * cos(deg2rad($driver->latitude)) *
            sin($lng_diff / 2) * sin($lng_diff / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earth_radius * $c;

        $distance = round($distance, 2);

        // Append the distance in search ride
        $ride->customer_distance = $distance;

        return response()->json(['status' => 'success','ride' => $ride],200);
    }

    public function reachPickupLocation(Request $request){

        $validate = DriverAppValidation::reachPickupLocationValidation($request);

        if($validate['status'] == "error"){
            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $ride = Ride::find($request->ride_id);
        $ride->status = 4;//4->reached to pickup
        $ride->reached_pickup_time = now();
        $ride->save();

        $user = User::find($ride->user_id);

        $title = "AutoKaar";

        $msg = "Dear ".$user->name.", Driver ".$ride->driver->name." has arrived at your pick-up location.";

        DriverUserNotificationEvent::dispatch($user, $ride->id, $title, $msg,null,'mrautokaar');

        return response()->json(['status' => 'success','message' => 'Driver Reached to Pickup Location'],200);
    }

    public function rideOtpVerify(Request $request){

        $validate = DriverAppValidation::rideOtpVerifyValidation($request);

        if($validate['status'] == "error"){
            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }

        $ride = Ride::find($request->ride_id);

        if($request->otp != $ride->otp){

            return response()->json(['status' => 'error','message' => 'Otp Not Matches!!'],400);

        }else{
            $ride->ride_started_time = now();
            $ride->status = 5; //otp verified,ride start
            $ride->save();

            $user = User::find($ride->user_id);

            $title = "AutoKaar";

            $msg = "Hi ".$ride->user->name.", your ride starts now. Your estimated arrival time is ".$ride->total_hrs.". We hope you have a safe ride!";

            DriverUserNotificationEvent::dispatch($user, $ride->id, $title, $msg,null,'mrautokaar');

            $admin_notify = "The Ride #  otp validated and ride started !";

            AdminNotificationSend::dispatch("Ride #",$admin_notify,$ride->id);

            return response()->json(['status' => 'success','message' => 'Otp Matched Successfully'],200);
        }
    }

    public function reachDropLocation(UserRideInterface $UserRideService,Request $request){
        $validate = DriverAppValidation::reachDropLocationValidation($request);

        if($validate['status'] == "error"){
            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $driver = auth()->user();

        $ride = Ride::find($request->ride_id);

        $user = User::find($ride->user_id);

        $response = $UserRideService->rideFare($request,$ride);

        if($request->status == 6) {//6->ride now drop off
            $ride->status = $request->status;
            $ride->save();

            $billing = RideBillingDetailSave::dispatch($request,$ride,$response);

            $title = "AutoKaar";

            if($billing[0])
            $msg = "Hi ".$ride->user->name.", your ride has been ended. You have reached the destination. Please make the ride payment of ".$billing[0]->amount." INR to ".$ride->driver->name.".";

            DriverUserNotificationEvent::dispatch($user, $ride->id, $title, $msg, null,'mrautokaar');

            $message = "Hi ".$driver->name.", the ride has been ended. You have reached the destination. Please collect the ride payment of ".$billing[0]->amount." INR.

            Note: You are responsible to collect the payment from the passenger through online or cash.";

            DriverUserNotificationEvent::dispatch($driver, $ride->id, $title, $message,null, 'mrautokaar');

            // if(env('APP_ENV') != 'localhost'){

            //     $msg = "Hi ".$user->name.", your ride has ended. You have reached the destination. Please make the ride payment of ".$billing[0]->amount." INR to the ATOKAR Driver.";

            //     SendSmsEvent::dispatch('1207168121933855271',$user->mobile, $msg);
            // }
        }
        elseif($request->status == 11){//change trip drop off

            $ride->status = $request->status;
            $ride->save();

            $ride_detail = RideDetail::where('ride_id',$ride->id)->first();

            $ride_detail->end_date_time = now();

            $ride_detail->save();

            $ride_bill = RideBillingDetail::where('ride_id',$ride->id)->orderBy('id','DESC')->take(1)->delete();

            $ride_billing = RideBillingDetailSave::dispatch($request,$ride,$response);

            $billing_amt = RideBillingDetail::where('ride_id',$ride->id)->sum('amount');

            $ride->final_amount = $billing_amt;

            $ride->save();

            $title = "AutoKaar";

            $msg = "Hi ".$user->name.", your ride has been ended. You have reached the destination. Please make the ride payment of ".$billing_amt." INR to ".$ride->driver->name.".";

            DriverUserNotificationEvent::dispatch($user, $ride->id, $title, $msg,null,'mrautokaar');

            // if(env('APP_ENV') != 'localhost'){

            //     $msg = "Hi ".$user->name.", your ride has been ended. You have reached the destination. Please make the ride payment of ".$billing_amt." INR to ".$ride->driver->name.".";

            //     SendSmsEvent::dispatch('1207167454882087705',$user->mobile, $msg);
            // }
        }

        DriverPaymentStore::dispatch($ride);

        return response()->json(['status' => 'success','message' => 'Reach to Drop Location Successfully'],200);
    }

    public function qrPay(Request $request){

        $validate = DriverAppValidation::qrPayValidation($request);

        if($validate['status'] == "error"){
            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $ride = Ride::find($request->ride_id);
        $driver = Driver::with('driverDetail')->find($ride->driver_id);

        $upi_id = $driver->driverDetail->upi_id ?? 'null';

        $upi_number = $driver->mobile;

        $qr_code = $driver->driverDetail->img_url ?? 'null';

        return response()->json(['status' => 'success','upi_id' => $upi_id, 'upi_number' => $upi_number, 'qr_code' => $qr_code],200);
    }

    public function qrPaymentConfirm(Request $request){
    //add payment type
        $validate = DriverAppValidation::qrPaymentConfirmValidation($request);

        if($validate['status'] == "error"){
            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $driver = auth()->user();

        $ride = Ride::find($request->ride_id);

        if($request->status == 10 || $request->status == 12)
        $ride->status = $request->status; //10->ride now completed,12->change ride completed
        $ride->save();

        GiftReward::dispatch($ride);

        $admin_notify = "The Ride # Has Been Completed and Payment paid Successfully !";

        $user = User::find($ride->user_id);

        $title = "AutoKaar";

        $msg = "Hi ".$ride->user->name.", please give us a minute to rate your driver and the ride.

        This will help us to improve your rides further.";

        DriverUserNotificationEvent::dispatch($user, $ride->id, $title, $msg,null,'mrautokaar');

        $message = "Hi ".$driver->name.", please give us a minute to rate your passenger and the ride.

        This will help us to improve your rides further.";

        DriverUserNotificationEvent::dispatch($driver, $ride->id, $title, $message,null,'mrautokaar');

        AdminNotificationSend::dispatch("Ride #",$admin_notify,$ride->id);

        return response()->json(['status' => 'success','message' => 'Driver Acknowledge Successfully'],200);
    }

    public function addRideRequest(Request $request){

        $validate = DriverAppValidation::addRideRequestValidation($request);

        if($validate['status'] == "error"){
            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $driver = auth()->user();

        $ride = Ride::with('rideDetail','rideBillingDetails')->find($request->ride_id);

        $detail = RideDetail::where('ride_id',$request->ride_id)->first();

        $user_name = $detail->ride->user->name;

        return response()->json(['status' => 'success','user_name'=>$user_name, 'ride' => $ride],200);
    }

    public function addRideAcceptReject(UserRideInterface $UserRideService,Request $request){

        $validate = DriverAppValidation::addRideAcceptRejectValidation($request);

        if($validate['status'] == "error"){
            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $ride = Ride::find($request->ride_id);

        $response = $UserRideService->rideFare($request,null);

        if($request->status == 8){//change destination accept

            $ride->status = $request->status;
            $ride->save();

            // $ride_billing = RideBillingDetail::where('ride_id',$ride->id)->latest()->first();

            // $ride_billing->delete();

            // RideBillingDetailSave::dispatch($request,$ride,$response);

            $driver = Driver::find($ride->driver_id);

            $user = User::find($ride->user_id);

            $title = "AutoKaar";

            $mesg = "Dear ".$user->name.", Driver ".$ride->driver->name." will arrive shortly to your pick-up location.";

            DriverUserNotificationEvent::dispatch($user, $ride->id, $title, $mesg,null,'mrautokaar');

            $message = "Hi ".$driver->name.", thank you for accepting the ride request. Drive Safely!

            Please collect the OTP from the passenger and enter it to start your ride.";

            DriverUserNotificationEvent::dispatch($driver, $ride->id, $title, $message,null,'mrautokaar');

            $admin_notify = "The Ride #  has been accepted by" .$driver->name. " .";

            AdminNotificationSend::dispatch("Ride #",$admin_notify,$ride->id);

            if(env('APP_ENV') != 'localhost'){

                $msg = "Hi ".$ride->user->name.", your ride request was accepted by driver ".$ride->driver->name.". To start your ride, please share your OTP ".$ride->otp.".

                Note: Share the OTP only when starting your ride.

                You can share your ride details with your friends or family members. Tap to Share ride details to SOS contact.";

                SendSmsEvent::dispatch('1207168121911398159',$ride->user->mobile, $msg);
            }
            
            return response()->json(['status' => 'success','message' => 'Ride Accepted'],200);

        }else{ //9->change destination reject

            $ride->status = $request->status;
            $ride->save();

            $user = User::find($ride->user_id);

            $ride_billings = RideBillingDetail::where('ride_id',$ride->id)->delete();

            $message = "Hi ".$user->name.", Your Another Trip Request Has Been Rejected By Driver ".$ride->driver->name."";

            DriverUserNotificationEvent::dispatch($user, $ride->id, 'AutoKaar', $message,null,'mrautokaar');

            return response()->json(['status' => 'success','message' => 'Ride Rejected'],200);
        }
    }

    public function driverReviewStore(UserRideInterface $UserRideService,Request $request){

        $validate = DriverAppValidation::driverReviewStoreValidation($request);

        if($validate['status'] == "error"){
            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $ride = Ride::find($request->ride_id);

        $response = $UserRideService->rideReview($request,$ride->id);

        return response()->json(['status' => $response['status'], 'message' => 'Review Send Submit Successfully'],200);
    }

    public function rideDetail($ride_id){

        $ride = Ride::with('rideDetail','rideBillingDetails','user:id,name,image,mobile')->with('rideReview', function($q){

            $q->whereNotNull('user_rating');

        })->find($ride_id);

        $driver = auth()->user();

        $earth_radius = 6371; // in kilometers

        $lat_diff = deg2rad($driver->latitude - $ride->pickup_latitude);
        $lng_diff = deg2rad($driver->longitude - $ride->pickup_longitude);
        $a = sin($lat_diff / 2) * sin($lat_diff / 2) +
            cos(deg2rad($ride->pickup_latitude)) * cos(deg2rad($driver->latitude)) *
            sin($lng_diff / 2) * sin($lng_diff / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earth_radius * $c;

        $distance = round($distance, 2);

        // Append the distance in search ride
        $ride->customer_distance = $distance;

        return response()->json(['status' => 'success', 'ride' => $ride],200);
    }

    public function cancelRide(UserRideInterface $UserRideService,Request $request){

        $validate = DriverAppValidation::cancelRideValidation($request);

        if($validate['status'] == "error"){

            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }

        $ride = Ride::find($request->ride_id);

        if($request->status == 2){//ride cancelled

            $ride->status = $request->status;

            $ride->save();
        }

        $user = user::find($ride->user_id);

        $msg = "Hi ".$user->name.",The Ride Has Been Canceled By Driver";

        DriverUserNotificationEvent::dispatch($user, $ride->id, 'AutoKaar', $msg, 'driver-cancel-ride','mrautokaar');

        $admin_notify = "The Ride # Has Been cancelled By Driver !";

        AdminNotificationSend::dispatch("Ride #",$admin_notify,$ride->id);

        return response()->json(['status' => 'success', 'message' => 'Ride Has Been Cancelled'],200);
    }


}
