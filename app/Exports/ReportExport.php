<?php

namespace App\Exports;

use App\Models\SearchRide;
use App\Models\User;
use App\Models\UserReward;
use App\Models\Ride;
use App\Models\Subscription;
use App\Models\Driver;
use App\Models\DriverPayment;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ReportExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $request;

    function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $request = $this->request;

        if($request->type == 'bookings'){

            if($request->status == 0){

                $reports = SearchRide::where('status',3)->with('user')->when($request->start_date,function($query,$start)
                {
                $query->whereDate('created_at','>=',$start);

                })->when($request->end_date,function($query,$to){

                $query->whereDate('created_at','<=',$to);

                })->get();//reject

                return view('exports.search_ride_report', compact('reports'));

            }elseif($request->status == 1){

                $reports = SearchRide::whereIn('status',[0,1])->with('user')->when($request->start_date,function($query,$start)
                {
                $query->whereDate('created_at','>=',$start);

                })->when($request->end_date,function($query,$to){

                $query->whereDate('created_at','<=',$to);

                })->get();//missed

                return view('exports.search_ride_report', compact('reports'));

            }elseif($request->status == 2){

                $reports = Ride::whereIn('status',[2,9])->with('user')->with(['driver.driverProofs' => function($q){

                    $q->where('type',1);//vachicle no.

                }])->when($request->start_date,function($query,$start)
                {
                $query->whereDate('created_at','>=',$start);

                })->when($request->end_date,function($query,$to){

                $query->whereDate('created_at','<=',$to);

                })->get();//cancel

                return view('exports.ride_report', compact('reports'));

            }elseif($request->status == 3){

                $reports = Ride::whereIn('status',[10,12])->with('user')->with(['driver.driverProofs' => function($q){

                    $q->where('type',1);//vachicle no.

                }])->when($request->start_date,function($query,$start)
                {
                $query->whereDate('created_at','>=',$start);

                })->when($request->end_date,function($query,$to){

                $query->whereDate('created_at','<=',$to);

                })->get();//completed ride

                return view('exports.ride_report', compact('reports'));

            }elseif($request->status == 4){

                $reports = Ride::with('user','rideDetail')->with('driver.driverProofs', function ($q){
                    $q->where('type',1);//vachicle

                })->when($request->start_date,function($query,$start)
                {
                $query->whereDate('created_at','>=',$start);

                })->when($request->end_date,function($query,$to){

                $query->whereDate('created_at','<=',$to);

                })->where('ride_type',2)->get();//schedule booking

                return view('exports.ride_report', compact('reports'));

            }else{

                $reports = Ride::with('user','rideDetail')->with('driver.driverProofs', function ($q){
                    $q->where('type',1);

                })->where('ride_type',3)->when($request->start_date,function($query,$start)
                {
                   $query->whereDate('created_at','>=',$start);

                })->when($request->end_date,function($query,$to){

                   $query->whereDate('created_at','<=',$to);

                })->where('ride_type',3)->get();//offline booking

                return view('exports.ride_report', compact('reports'));
            }

        }elseif($request->type == 'rewards'){

            $reports = UserReward::whereIn('status',[3,4])->with('user')->when($request->start_date,function($query,$start)
            {
                $query->whereDate('created_at','>=',$start);

            })->when($request->end_date,function($query,$to){

                $query->whereDate('created_at','<=',$to);

            })->get(); //3->requested, 4->credited

            return view('exports.reward_report', compact('reports'));

        }elseif($request->type == 'pay_tax'){

            $reports = DriverPayment::where('status',$request->status)->with('driver')->when($request->start_date,function($query,$start)
            {
                $query->whereDate('created_at','>=',$start);

            })->when($request->end_date,function($query,$to){

                $query->whereDate('created_at','<=',$to);

            })->get();

            return view('exports.pay_tax_report', compact('reports'));

        }elseif($request->type == 'activity_log'){

            $reports = Driver::with(['driverProofs' => function($q){

                $q->where('type',1);//vachicle no.

            }])->with('driverDetail')->when($request->start_date,function($query,$start)
            {
            $query->whereDate('created_at','>=',$start);

            })->when($request->end_date,function($query,$to){

            $query->whereDate('created_at','<=',$to);

            })->get();

            return view('exports.driver_logs_report', compact('reports'));

        }elseif($request->type == 'refferals'){

            if($request->status == 1){

                $reports = User::whereNotNull('refferal_id')->with('userRefferal:id,refferal_id,name,image,created_at,email,primary_mobile')->when($request->start_date,function($query,$start)
                {
                 $query->whereDate('created_at','>=',$start);

                })->when($request->end_date,function($query,$to){

                 $query->whereDate('created_at','<=',$to);

                })->get();

            }else{

                $reports = Driver::whereNotNull('refferal_id')->with('refferer:id,refferal_id,name,image,created_at,email,primary_mobile')->when($request->start_date,function($query,$start)
                {
                    $query->whereDate('created_at','>=',$start);

                })->when($request->end_date,function($query,$to){

                    $query->whereDate('created_at','<=',$to);

                })->get();
            }

            return view('exports.referal_report', compact('reports'));

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

            return view('exports.subscription_report', compact('reports'));
        }

    }
}
