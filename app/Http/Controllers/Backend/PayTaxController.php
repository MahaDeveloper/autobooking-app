<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Models\DriverPayment;
use App\Http\Controllers\Controller;

class PayTaxController extends Controller
{
    public function payTaxList(Request $request){

        $pay_taxes = DriverPayment::whereIn('status',[3,2])->with('driver.driverProofs', function($q){
            $q->where('type',1);//vachicle

        })->selectRaw("*,JSON_LENGTH(ride_ids) as total_ride")->when($request->start_date,function($query,$start)
        {
        $query->whereDate('created_at','>=',$start);

        })->when($request->end_date,function($query,$to){

        $query->whereDate('created_at','<=',$to);

        })->get(); //3-paid, 2->pending

        // $a = DriverPayment::with('transaction')->latest()->first();

        // return $a;

        return response()->json(['status' => 'success','pay_taxes' => $pay_taxes],200);
    }

    public function paidTaxHistory(Request $request){

        $paid_taxes = DriverPayment::where('status',3)->with('driver.driverProofs', function($q){
            $q->where('type',1);//vachicle
        })->selectRaw("*,JSON_LENGTH(ride_ids) as total_ride")->get(); //paid

        return response()->json(['status' => 'success','paid_taxes' => $paid_taxes],200);
    }

}
