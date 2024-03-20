<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\DriverResource;
use App\Models\Driver;
use App\Models\DriverPayment;
use App\Models\DriverLog;
use App\Models\DriverSubscription;
use App\Models\DriverProof;
use App\Models\Setting;
use App\Models\Ride;
use App\Models\RideReview;
use App\Models\RideBillingDetail;
use App\Helpers\BackendValidation;
use Carbon\Carbon;
use App\Events\SendSmsEvent;
use Illuminate\Support\Facades\DB;
use App\Events\DriverUserNotificationEvent;

class DriverController extends Controller
{
    public function requestList(Request $request){

        $request_drivers = DriverResource::collection(Driver::with('driverDetail','driverProofs')->whereIn('verification_status',[0,1])->when($request->start_date,function($query,$start)
        {
           $query->whereDate('created_at','>=',$start);

        })->when($request->end_date,function($query,$to){

           $query->whereDate('created_at','<=',$to);

        })->get());//requested

        $reject_drivers = DriverResource::collection(Driver::with('driverDetail','driverProofs')->where('verification_status',3)->when($request->start_date,function($query,$start)
        {
           $query->whereDate('created_at','>=',$start);

        })->when($request->end_date,function($query,$to){

           $query->whereDate('created_at','<=',$to);

        })->get());//rejected

        return response()->json(['status' => 'success', 'request_drivers' => $request_drivers, 'reject_drivers' => $reject_drivers], 200);
    }

    public function requestRejectView($id){

        $driver = Driver::with('driverDetail','driverProofs')->find($id);

        return response()->json(['status' => 'success', 'driver' => $driver], 200);
    }

    public function proofApprove(Request $request){

        $validate = BackendValidation::proofApproveValidation($request);
        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }
        $driver = Driver::find($request->driver_id);

        $driver->verification_status = $request->verification_status;
        $driver->accepted_rejected_date = now();
        $driver->save();

        if($request->verification_status == 2){//accept

            $setting = Setting::where('type',1)->first();//subscription

            if($setting){

                $subscription_end_date = Carbon::today()->addDays($setting->value)->format('Y-m-d');

            }else{

                $subscription_end_date = Carbon::today()->addDays(31)->format('Y-m-d');
            }

            if(!$driver->subscription_end_date){ //not exist

                $driver->subscription_end_date = $subscription_end_date;

                $driver->save();
            }

            $title = "AutoKaar";

            $msg = "Hi ".$driver->name.", your account has been approved.

            Limited Free Subscription was offered. You can Go-Online!

            We ask you to drive safely and offer hassle free rides.

            Note: You are responsible to collect the ride payment from the passenger.";

            DriverUserNotificationEvent::dispatch($driver, null, $title, $msg,null,'mrautokaar');

            if(env('APP_ENV') != 'localhost'){

                $message = "Hi ".$driver->name.", your account has been approved.

                Limited Free Subscription was offered. You can Go-Online!

                We ask you to drive safely and offer hassle free rides.

                Note: You are responsible to collect the ride payment from the passenger.";

                SendSmsEvent::dispatch('1207167454882087705',$driver->mobile, $message);
            }

            return response()->json(['status' => 'success', 'message' => 'Proofs Accepted Successfully'], 200);

        }else{ //3->reject

            if($request->reject_reason){
                $driver = Driver::find($request->driver_id);
                $driver->reject_reason = $request->reject_reason;
                $driver->save();
            }
            if(env('APP_ENV') != 'localhost'){

                $message = "Hi ".$driver->name.", your approval request was denied.

                We are sorry to inform we are unable to approve your ATOKAR account. Please contact our support team (1800-123-6848) for more information.";

                SendSmsEvent::dispatch('1207168121976388148',$driver->mobile, $message);
            }

            return response()->json(['status' => 'success', 'message' => 'Proofs Rejected'], 200);
        }
    }

    public function acceptedDrivers(Request $request){

        $accepted_drivers = DriverResource::collection(Driver::with(['driverDetail','driverProofs'])->where('verification_status',2)->when($request->start_date,function($query,$start)
        {
           $query->whereDate('created_at','>=',$start);

        })->when($request->end_date,function($query,$to){

           $query->whereDate('created_at','<=',$to);

        })->get());//accepted

        return response()->json(['status' => 'success', 'accepted_drivers' => $accepted_drivers], 200);
    }

    public function driverProfile($id){

        $driver = Driver::with(['driverDetail','driverProofs'])->find($id);

        $driver_subsctiption = DriverSubscription::where('driver_id',$id)->where('status',1)->with('subscription')->first();

        $tax = Setting::where('type',2)->first();

        $tax_percentage = $tax->value;

        $total_referrals = Driver::where('refferal_id',$driver->id)->count() ?? 0;

        $total_tax_paid = DriverPayment::where('driver_id',$driver->id)->where('status',3)->sum('amount');

        $driver_log = DriverLog::select(
            'driver_logs.driver_id',
            DB::raw('SUM(TIMESTAMPDIFF(SECOND, driver_logs.check_in_time, driver_logs.check_out_time)) / 3600 as total_hours')
        )->where('driver_id',$driver->id)->get();

        $total_hours = $driver_log->sum('total_hours') ?? 0;

        $hours = floor($total_hours);
        $minutes = round(($total_hours - $hours) * 60);

        $total_login_hrs =  $hours.' Hours , ' .$minutes. ' Minutes';

        $ride = Ride::whereIn('status',[10,12])->where('driver_id',$driver->id)->get();//cancel

        $total_rides = $ride->count() ?? 0;

        $ride_ids = $ride->pluck('id')->toArray();

        $toatl_amount = RideBillingDetail::whereIn('ride_id',$ride_ids)->sum('amount');

        $estimated_tax_amt = RideBillingDetail::whereIn('ride_id',$ride_ids)->sum('fare_details->estimated_tax');

        $total_earned_amt_except_tax = $toatl_amount - round($estimated_tax_amt);

        $total_count = RideReview::whereIn('ride_id',$ride_ids)->whereNotNull('driver_rating')->count();

        $driver_ratings = RideReview::whereIn('ride_id',$ride_ids)->whereNotNull('driver_rating')->sum('driver_rating');

        if($total_count != 0){
            $total_ratings = round($driver_ratings / $total_count);
        }else{
            $total_ratings = 0;
        }

        $cancel_rides = Ride::whereIn('status',[2,9])->where('driver_id',$driver->id)->count() ?? 0;

        return response()->json(['status' => 'success', 'total_rides' =>$total_rides,'total_login_hrs' => $total_login_hrs,'total_referrals' => $total_referrals, 'total_tax_paid' => round($total_tax_paid),'tax_percentage' =>$tax_percentage, 'total_earned_amt' => null, 'total_ratings' => $total_ratings,'cancel_rides' => $cancel_rides, 'total_earned_amt_except_tax' => $total_earned_amt_except_tax,'driver_subsctiption' => $driver_subsctiption,'driver' => $driver], 200);
    }

    public function driverRideHistory(Request $request,$driver_id){

        $driver_ride_history = Ride::with('rideDetail:id,ride_id,pickup_address,drop_address','user:id,name,image')->where('driver_id',$driver_id)->when($request->start_date,function($query,$start)
        {
           $query->whereDate('created_at','>=',$start);

        })->when($request->end_date,function($query,$to){

           $query->whereDate('created_at','<=',$to);

        })->get();

        return response()->json(['status' => 'success','driver_ride_history' => $driver_ride_history],200);
    }

    public function driverRideHistoryView($ride_id){

        $driver_ride_history = Ride::with('rideDetail:id,ride_id,pickup_address,drop_address','user:id,name,image')->with('rideReview', function($q){
            $q->whereNull('user_rating')->whereNull('user_review');
        })->find($ride_id);

        return response()->json(['status' => 'success','driver_ride_history' => $driver_ride_history],200);
    }

    public function activityLog(){

        $drivers = Driver::with(['driverProofs' => function($q){

            $q->where('type',1);//vachicle no.

        }])->with('driverDetail')->where('verification_status',2)->get();

        return response()->json(['status' => 'success','drivers' => $drivers],200);
    }

    public function activityLogView($driver_id){

        $driver_log = DriverLog::select(
            'driver_logs.driver_id',
            DB::raw('DATE(driver_logs.check_in_time) as date'),
            DB::raw('SUM(TIMESTAMPDIFF(SECOND, driver_logs.check_in_time, driver_logs.check_out_time)) / 3600 as total_hours'),
            DB::raw('COUNT(DISTINCT received_rides.id) as received_rides'),
            DB::raw('COUNT(DISTINCT completed_rides.id) as completed_rides'),
            DB::raw('SUM(ride_earned.final_amount) as earned_amount')
        )
        ->leftJoin('rides as received_rides', function ($join) {
            $join->on('driver_logs.driver_id', '=', 'received_rides.driver_id')
                ->where('received_rides.status', '=', 10)
                ->whereRaw('DATE(driver_logs.check_in_time) = DATE(received_rides.created_at)');
        })
        ->leftJoin('rides as completed_rides', function ($join) {
            $join->on('driver_logs.driver_id', '=', 'completed_rides.driver_id')
                ->where('completed_rides.status', '=', 2)
                ->whereRaw('DATE(driver_logs.check_in_time) = DATE(completed_rides.created_at)');
        })
        ->leftJoin('rides as ride_earned', function ($join) {
            $join->on('driver_logs.driver_id', '=', 'completed_rides.driver_id')
                ->whereRaw('DATE(driver_logs.check_in_time) = DATE(completed_rides.created_at)');
        })
        ->where('driver_logs.driver_id','=',$driver_id)->groupBy('date') //driver_id
        ->get();

        return response()->json(['status' => 'success','driver_log' => $driver_log],200);
    }

    public function driverStatus($driver_id){

        $driver = Driver::find($driver_id);

        if($driver->current_status != 6){

            $driver->current_status = 6; // disable
            $driver->save();

            // $mesg = "The Driver Deactivated Successfully";

            $message = "Hi ".$driver->name.", your Mr. AutoKaar Driver account has been suspended.

            For more information, please contact our support team (1800-123-6848).";

            DriverUserNotificationEvent::dispatch($driver, null, 'AutoKaar', $message,null,'mrautokaar');

            if(env('APP_ENV') != 'localhost'){

                SendSmsEvent::dispatch('1207168130799987499',$driver->mobile, $message);
            }

        }elseif($driver->current_status != 0){

            $driver->current_status = 0; //active
            $driver->save();

            $message = "Hi ".$driver->name.", we are glad to inform your account has been re-activated successfully.

            Go-Online and start taking the rides!";

            DriverUserNotificationEvent::dispatch($driver, null, 'AutoKaar', $message,null,'mrautokaar');

            // $mesg = "The Driver Status Activated Successfully";
        }

        return response()->json(['status' => 'success','message' => 'Status Has Been Chanage Successfully'],200);
    }

}
