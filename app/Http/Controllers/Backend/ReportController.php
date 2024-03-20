<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ride;
use App\Models\User;
use App\Models\UserReward;
use App\Models\Support;
use App\Models\Subscription;
use App\Models\Driver;
use App\Models\DriverPayment;
use App\Models\SearchRide;
use Illuminate\Support\Facades\DB;
use App\Exports\ReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function filterReport(Request $request){

        if($request->type == 'bookings'){

            if($request->status == 0){

                $reports = SearchRide::where('status',3)->with('user:id,name,image')->when($request->start_date,function($query,$start)
                {
                $query->whereDate('created_at','>=',$start);

                })->when($request->end_date,function($query,$to){

                $query->whereDate('created_at','<=',$to);

                })->get();//reject

            }elseif($request->status == 1){

                $reports = SearchRide::whereIn('status',[0,1])->with('user:id,name,image')->when($request->start_date,function($query,$start)
                {
                $query->whereDate('created_at','>=',$start);

                })->when($request->end_date,function($query,$to){

                $query->whereDate('created_at','<=',$to);

                })->get();//missed

            }elseif($request->status == 2){

                $reports = Ride::whereIn('status',[2,9])->with('user:id,name,image','rideDetail:id,ride_id,pickup_address,drop_address')->with(['driver.driverProofs' => function($q){

                    $q->where('type',1);//vachicle no.

                }])->when($request->start_date,function($query,$start)
                {
                $query->whereDate('created_at','>=',$start);

                })->when($request->end_date,function($query,$to){

                $query->whereDate('created_at','<=',$to);

                })->get();//cancel

            }elseif($request->status == 3){

                $reports = Ride::whereIn('status',[10,12])->with('user:id,name,image','rideDetail:id,ride_id,pickup_address,drop_address')->with(['driver.driverProofs' => function($q){

                    $q->where('type',1);//vachicle no.

                }])->when($request->start_date,function($query,$start)
                {
                $query->whereDate('created_at','>=',$start);

                })->when($request->end_date,function($query,$to){

                $query->whereDate('created_at','<=',$to);

                })->get();//completed ride

            }elseif($request->status == 4){

                $reports = Ride::with('user:id,name,image','rideDetail:id,ride_id,pickup_address,drop_address')->with('driver.driverProofs', function ($q){
                    $q->where('type',1);//vachicle

                })->when($request->start_date,function($query,$start)
                {
                $query->whereDate('created_at','>=',$start);

                })->when($request->end_date,function($query,$to){

                $query->whereDate('created_at','<=',$to);

                })->where('ride_type',2)->get();//schedule booking

            }else{

                $reports = Ride::with('user:id,name,image','rideDetail:id,ride_id,pickup_address,drop_address')->with('driver.driverProofs', function ($q){
                    $q->where('type',1);

                })->where('ride_type',3)->when($request->start_date,function($query,$start)
                {
                   $query->whereDate('created_at','>=',$start);

                })->when($request->end_date,function($query,$to){

                   $query->whereDate('created_at','<=',$to);

                })->where('ride_type',3)->get();//offline booking
            }

        }elseif($request->type == 'rewards'){

            $reports = UserReward::whereIn('status',[3,4])->with('user:id,name,image,email,mobile')->when($request->start_date,function($query,$start)
            {
            $query->whereDate('created_at','>=',$start);

            })->when($request->end_date,function($query,$to){

            $query->whereDate('created_at','<=',$to);

            })->get();//3->requested, 4->credited

        }elseif($request->type == 'pay_tax'){

            $reports = DriverPayment::where('status',$request->status)->with('driver:id,name,image,email')->with('driver.driverProofs', function($q){
                $q->where('type',1);//vachicle
            })->selectRaw("*,JSON_LENGTH(ride_ids) as total_ride")->when($request->start_date,function($query,$start)
            {
            $query->whereDate('created_at','>=',$start);

            })->when($request->end_date,function($query,$to){

            $query->whereDate('created_at','<=',$to);

            })->get();

        }elseif($request->type == 'activity_log'){

            $reports = Driver::with(['driverProofs' => function($q){

                $q->where('type',1);//vachicle no.

            }])->with('driverDetail')->when($request->start_date,function($query,$start)
            {
            $query->whereDate('created_at','>=',$start);

            })->when($request->end_date,function($query,$to){

            $query->whereDate('created_at','<=',$to);

            })->get();

        }elseif($request->type == 'refferals'){

            if($request->status == 1){

                $reports = User::whereNotNull('refferal_id')->with('userRefferal:id,refferal_id,name,image,created_at')->when($request->start_date,function($query,$start)
                {
                 $query->whereDate('created_at','>=',$start);

                })->when($request->end_date,function($query,$to){

                 $query->whereDate('created_at','<=',$to);

                })->get();

            }elseif($request->status == 2){

                $reports = Driver::whereNotNull('refferal_id')->with('refferer:id,refferal_id,name,image,created_at')->when($request->start_date,function($query,$start)
                {
                $query->whereDate('created_at','>=',$start);

                })->when($request->end_date,function($query,$to){

                $query->whereDate('created_at','<=',$to);

                })->get();
            }

        }else{

            if($request->status == 1){

                $reports = Subscription::where('status',1)->when($request->start_date,function($query,$start)
                {
                $query->whereDate('created_at','>=',$start);

                })->when($request->end_date,function($query,$to){

                $query->whereDate('created_at','<=',$to);

                })->get();//active

            }else{

                $reports = Subscription::when($request->start_date,function($query,$start)
                {
                $query->whereDate('created_at','>=',$start);

                })->when($request->end_date,function($query,$to){

                $query->whereDate('created_at','<=',$to);

                })->get();//all
            }
        }

        return response()->json(['status' => 'success','reports' => $reports ?? null],200);
    }

    public function export(Request $request){

        return Excel::download(new ReportExport($request), 'reports.xlsx');
    }

}
