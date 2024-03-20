<?php

namespace App\Http\Controllers\Frontend\Driver;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\DriverPaymentResource;
use App\Models\DriverPayment;
use App\Models\Transaction;
use App\Models\Setting;
use App\Models\Ride;
use App\Models\RideBillingDetail;
use App\Events\TransactionSave;
use App\Helpers\DriverAppValidation;
use Log;

class DriverPayTaxController extends Controller
{
    public function payTaxPayment(Request $request){

        $validate = DriverAppValidation::payTaxValidation($request);

        if($validate['status'] == "error"){

            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }

        $driver = auth()->user();

        $pay_tax = DriverPayment::find($request->pay_tax_id);

        $transaction = new Transaction();
        $transaction->driver_id = $driver->id;
        $transaction->amount = round($request->amount);
        $transaction->save();
        $order_id = TransactionSave::dispatch($transaction->id);

        if($order_id[0]){

            $driver->current_status = 0; //active
            $driver->save();
        }

        return response()->json(['status' => 'success', 'message' => 'Transansaction Save Successfully', 'order_id' => $order_id[0]],200);
    }

    public function payTaxList(Request $request){

        $driver = auth()->user();

        $current_pay_tax = DriverPayment::whereIn('status',[1,2])->where('driver_id',$driver->id)->first();//filling or reached

        $paid_taxes = DriverPayment::where('status',3)->where('driver_id',$driver->id)
        ->when($request->date,function($query,$date){
           $query->whereDate('updated_at',$date);
        })
        ->get();

        $driver_id = DriverPayment::pluck('driver_id')->toArray();

        $rides = Ride::where('driver_id',$driver->id)->whereIn('status',[10,12])->get();

        $total_rides = $rides->count() ?? 0;

        $ride_ids = $rides->pluck('id')->toArray();

        $total_earned_amt = RideBillingDetail::whereIn('ride_id',$ride_ids)->sum('amount');

        return response()->json(['status' => 'success', 'total_rides' => $total_rides, 'total_earned_amt' => $total_earned_amt,'current_pay_tax' => $current_pay_tax ,'paid_taxes' => $paid_taxes],200);
    }

}
