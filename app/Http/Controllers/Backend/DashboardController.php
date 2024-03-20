<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ride;
use App\Models\User;
use App\Models\Support;
use App\Models\Subscription;
use App\Models\Driver;
use App\Models\SearchRide;
use App\Models\DriverPayment;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function dashboardCounts(){

        $a = User::active()->get();

        $users = count($a) ??  0;

        $b = Ride::whereHas('user')->groupBy('user_id')->get();

        $passengers  = count($b) ??  0;

        $c = Driver::where('current_status',1)->get();

        $active_drivers  = count($c) ??  0;

        $d = Driver::where('current_status','!=',6)->get();

        $drivers  = count($d) ??  0;

        $e = Driver::where('verification_status',2)->get();

        $approved_drivers  = count($e) ??  0;

        $f = Driver::where('verification_status',0)->get();

        $pending_approval_drivers  = count($f) ??  0;

        $g = Ride::whereIn('status',[10,12])->get();

        $completed_rides  = count($g) ??  0;

        $h = Ride::whereIn('status',[1,3,5,4,6,8])->get();

        $active_rides  = count($h) ??  0;
        //ask search add on ah

        $i = Driver::where('current_status',2)->get();

        $offline_drivers  = count($i) ??  0;

        return response()->json(['status' => 'success', 'users' => $users, 'passengers' => $passengers, 'active_drivers' => $active_drivers, 'drivers' => $drivers, 'approved_drivers' => $approved_drivers, 'pending_approval_drivers' => $pending_approval_drivers, 'completed_rides' => $completed_rides, 'active_rides' => $active_rides,'offline_drivers' => $offline_drivers], 200);
    }

    public function countPercentage(){

        $total_support = Support::count();

        $open_support = Support::where('status',0)->count();

        $close_support = Support::where('status',1)->count();

        $active_subscriptions = Subscription::active()->count();

        $total_subscriptions = Subscription::count();

        $missed_rides = SearchRide::whereIn('status',[0,1])->count();

        $rejected_rides = SearchRide::where('status',3)->count();

        $total_rides = SearchRide::count();

        $cancelled_rides = Ride::whereIn('status',[2,9])->count();

        $active_subs_percentage =  round(($active_subscriptions /$total_subscriptions) * 100);

        $open_support_percentage = round(($open_support / $total_support)) * 100;

        $close_support_percentage = round(($close_support / $total_support)) * 100;

        $cancelled_ride_percentage = round(($cancelled_rides / $total_rides)) * 100;

        $rejected_ride_percentage = round(($rejected_rides / $total_rides)) * 100;

        $missed_ride_percentage = round(($missed_rides / $total_rides)) * 100;

        return response()->json(['status' => 'success', 'total_support' => $total_support, 'open_support' => $open_support, 'close_support' => $close_support, 'active_subscriptions' => $active_subscriptions, 'total_subscriptions' => $total_subscriptions, 'cancelled_rides' => $cancelled_rides,'missed_rides' => $missed_rides, 'rejected_rides' => $rejected_rides, 'active_subs_percentage' => $active_subs_percentage, 'open_support_percentage' => $open_support_percentage, 'close_support_percentage' => $close_support_percentage, 'cancelled_ride_percentage' => $cancelled_ride_percentage,'rejected_ride_percentage' => $rejected_ride_percentage, 'missed_ride_percentage' => $missed_ride_percentage], 200);
    }

    public function revenueGraph(Request $request){

        $rides = Ride::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(final_amount) as revenue'))
            ->when($request->start_date,function($query,$start)
            {
            $query->whereDate('created_at','>=',$start);

            })->when($request->end_date,function($query,$to){

            $query->whereDate('created_at','<=',$to);

            })->groupBy('date')->get();

        $revenue = [];
        $date = [];

        foreach ($rides as $ride) {
            $revenue[] = $ride->revenue;
            $date[] = $ride->date;
        }

        return response()->json(['status' => 'success', 'revenue' => $revenue, 'date' => $date], 200);
    }

    public function payTaxGraph(Request $request){

        $pay_taxs = DriverPayment::where('status',3)->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as pay_tax'))
            ->when($request->start_date,function($query,$start)
            {
            $query->whereDate('created_at','>=',$start);

            })->when($request->end_date,function($query,$to){

            $query->whereDate('created_at','<=',$to);

            })->groupBy('date')->get();

        $tax_amt = [];
        $date = [];

        foreach ($pay_taxs as $pay_tax) {
            $tax_amt[] = $pay_tax->pay_tax;
            $date[] = $pay_tax->date;
        }

        return response()->json(['status' => 'success', 'tax_amt' => $tax_amt, 'date' => $date], 200);
    }


}
