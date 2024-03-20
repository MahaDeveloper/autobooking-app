<?php

namespace App\Http\Controllers\Frontend\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ride;
use App\Models\Driver;
use App\Models\DriverLog;
use App\Models\RideBillingDetail;
use App\Models\UserDriverNotification;
use DB;

class DriverController extends Controller
{
    public function rideHistory(Request $request){

        $driver = auth()->user();

        $ride_history = Ride::with('user:id,name,image','rideDetail')->where('driver_id',$driver->id)->when($request->start_date,function($query,$start){
            $query->whereDate('created_at','>=',$start);
        })->when($request->end_date,function($query,$to){
             $query->whereDate('created_at','<=',$to);
        })->orderBy('id','DESC')->get();

        $total_rides = $ride_history->count();

        $ride_ids = Ride::where('driver_id',$driver->id)->whereIn('status',[10,12])->pluck('id')->toArray();

        $total_earned_amt = RideBillingDetail::whereIn('ride_id',$ride_ids)->sum('amount');

        return response()->json(['status' => 'success', 'total_rides' => $total_rides, 'total_earned_amt' => $total_earned_amt,'ride_history' => $ride_history], 200);
    }

    public function rideHistoryView($ride_id){

        $ride_detail = Ride::with('user:id,name,image','rideDetail','rideBillingDetails')->with('rideReview', function($q){

            $q->whereNotNull('user_rating');
        })->find($ride_id);

        return response()->json(['status' => 'success', 'ride_detail' => $ride_detail], 200);
    }

    public function driverHomePage(){

        $driver = auth()->user();

        $ride = Ride::whereIn('status',[10,12])->with(['driver.driverProofs' => function($q){

            $q->where('type',1);//vachicle no.

        }])->where('driver_id',$driver->id)->get();//cancel

        $driver_detail = Driver::with(['driverProofs'=> function($q){

            $q->where('type',1);//vachicle no.

        }])->find($driver->id);

        $total_rides = $ride->count() ?? 0;

        $ride_ids = $ride->pluck('id')->toArray();

        $total_earned_amt = RideBillingDetail::whereIn('ride_id',$ride_ids)->sum('amount') ?? 0;

        $total_distance = round($ride->sum('distance')) ?? 0;

        $driver_log = DriverLog::select(
            'driver_logs.driver_id',
            DB::raw('DATE(driver_logs.check_in_time) as date'),
            DB::raw('SUM(TIMESTAMPDIFF(SECOND, driver_logs.check_in_time, driver_logs.check_out_time)) / 3600 as total_hours'))
            ->where('driver_logs.driver_id','=',$driver->id)->groupBy('date')
            ->get();

        $total_hours = $driver_log->sum('total_hours') ?? 0;

        $hours = floor($total_hours);
        $minutes = round(($total_hours - $hours) * 60);

        return response()->json(['status' => 'success', 'total_rides' => $total_rides, 'total_distance' => $total_distance ,'total_hours' => $hours, 'total_minutes' => $minutes, 'total_earned_amt' => $total_earned_amt,'driver_detail' => $driver_detail], 200);
    }

    public function notificationList(){

        $driver = auth()->user();

        $notifications = UserDriverNotification::where('notifiable_id',$driver->id)->orderBy('id','DESC')->where('notifiable_type','App\\Models\\Driver')->get();

        foreach($notifications as $notify){

            $notify->read_status = 1; //read

            $notify->save();
        }

        return response()->json(['status' => 'success','notifications'=> $notifications ],200);
    }

    public function notificationReadStatus(){

        $driver = auth()->user();

        $notifications = UserDriverNotification::where('notifiable_id',$driver->id)->where('read_status',0)->where('notifiable_type','App\\Models\\Driver')->count(); //not-view-count

        if($notifications == 0){

            $read_status = 1; //view
        }else{

            $read_status = 0; //not-view
        }

        return response()->json(['status' => 'success','read_status'=> $read_status],200);
    }



}
