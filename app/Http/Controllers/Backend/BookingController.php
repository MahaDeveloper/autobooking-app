<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ride;
use App\Models\Driver;
use App\Models\SearchRide;
use App\Models\RideReview;
use DB;
use PDF;

class BookingController extends Controller
{
    public function bookings(Request $request){

        $bookings = Ride::with('user:id,name,image','rideDetail:id,ride_id,pickup_address,drop_address')->with('driver.driverProofs', function ($q){

            $q->where('type',1);//vachicle

        })->when($request->start_date,function($query,$start)
        {
           $query->whereDate('created_at','>=',$start);

        })->when($request->end_date,function($query,$to){

           $query->whereDate('created_at','<=',$to);

        })->get();

        return response()->json(['status' => 'success','bookings' => $bookings],200);
    }

    public function bookingView($ride_id){

        $booking = Ride::with('user:id,name,image','rideDetail:id,ride_id,pickup_address,drop_address','rideReview')->with('driver.driverProofs', function ($q){
            $q->where('type',1);

        })->find($ride_id);

        return response()->json(['status' => 'success','booking' => $booking],200);
    }

    public function preBookings(Request $request){

        $pre_bookings = SearchRide::with('user:id,name,image')->when($request->start_date,function($query,$start)
        {
           $query->whereDate('created_at','>=',$start);

        })->when($request->end_date,function($query,$to){

           $query->whereDate('created_at','<=',$to);

        })->where('status',4)->get();//prebooking

        return response()->json(['status' => 'success','pre_bookings' => $pre_bookings],200);
    }

    public function cancelRides(Request $request){

        //rejected ride

        $rejected_rides = SearchRide::where('status',3)->with('user:id,name,image')->when($request->start_date,function($query,$start)
        {
           $query->whereDate('created_at','>=',$start);

        })->when($request->end_date,function($query,$to){

           $query->whereDate('created_at','<=',$to);

        })->get();//3->cancel

        //missed ride

        $missed_rides = SearchRide::whereIn('status',[0,1])->with('user:id,name,image')->when($request->start_date,function($query,$start)
        {
           $query->whereDate('created_at','>=',$start);

        })->when($request->end_date,function($query,$to){

           $query->whereDate('created_at','<=',$to);

        })->get();//0->search,1->driver not allocate

         //cancel ride

        $cancel_rides = Ride::whereIn('status',[2,9])->with('user:id,name,image')->with(['driver.driverProofs' => function($q){

            $q->where('type',1);//vachicle no.

        }])->when($request->start_date,function($query,$start)
        {
           $query->whereDate('created_at','>=',$start);

        })->when($request->end_date,function($query,$to){

           $query->whereDate('created_at','<=',$to);

        })->with('rideDetail')->get();//2->cancel ride,9->change trip reject

        return response()->json(['status' => 'success','cancel_rides' => $cancel_rides, 'rejected_rides' => $rejected_rides, 'missed_rides' => $missed_rides],200);
    }

    public function rejectRideView($search_ride_id){

        $reject_ride = SearchRide::with('user:id,name,image')->find($search_ride_id);

        $rejected_ids =  json_decode($reject_ride->rejected_drivers);

        $rejected_drivers = Driver::whereIn('id',$rejected_ids)->with('driverProofs', function ($q){

            $q->where('type',1);

        })->get();

        return response()->json(['status' => 'success','reject_ride' => $reject_ride, 'rejected_drivers' => $rejected_drivers],200);
    }

    public function cancelRideView($ride_id){

        $cancel_ride = Ride::with('user:id,name,image')->with('driver.driverProofs', function ($q){

            $q->where('type',1);

        })->with('rideDetail')->find($ride_id);

        return response()->json(['status' => 'success','cancel_ride' => $cancel_ride],200);
    }


    public function completedRide(Request $request){

        $completed_rides = Ride::with('user:id,name,image,email','rideDetail:id,ride_id,pickup_address,drop_address')->with('driver.driverProofs', function ($q){

            $q->where('type',1);//vachicle

        })->when($request->start_date,function($query,$start)
        {
           $query->whereDate('created_at','>=',$start);

        })->when($request->end_date,function($query,$to){

           $query->whereDate('created_at','<=',$to);

        })->whereIn('status',[10,12])->get();

        return response()->json(['status' => 'success','completed_rides' => $completed_rides],200);
    }

    public function downloadPdf(Request $request){

        $completed_rides = Ride::with('user:id,name,image,email','rideDetail:id,ride_id,pickup_address,drop_address')->with('driver.driverProofs', function ($q){

            $q->where('type',1);//vachicle

        })->when($request->start_date,function($query,$start)
        {
           $query->whereDate('created_at','>=',$start);

        })->when($request->end_date,function($query,$to){

           $query->whereDate('created_at','<=',$to);

        })->whereIn('status',[10,12])->whereNotNull('driver_id')->whereNotNull('user_id')->get();

        $pdf = PDF::loadView('invoice.completed-rides',compact('completed_rides'));

        return $pdf->download('completed-bookings.pdf');
    }


}
