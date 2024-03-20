<?php

namespace App\Http\Controllers\Frontend\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\DriverSubscription;
use App\Events\TransactionSave;
use App\Helpers\DriverAppValidation;
use App\Events\DriverSubscriptionSave;
use App\Events\DriverPayTaxSave;
use Log;

class SubscriptionController extends Controller
{
    public function subscriptionPlan(){

        $driver = auth()->user();

       $driver_subscription = DriverSubscription::where('driver_id',$driver->id)->first();

       if($driver_subscription){
        $driver_subscription_plan = Subscription::find($driver_subscription->subscription_id);
       }

       $subscription_plans = SubscriptionResource::collection(Subscription::active()->get());

       return response()->json(['status' => 'success','subscription_end_date' => $driver->subscription_end_date, 'subscription_plans' => $subscription_plans, 'driver_subscription'=> $driver_subscription ?? 'null', 'driver_subscription_plan' => $driver_subscription_plan ?? 'null'],200);
    }

    public function subscriptionPayment(Request $request){

        $validate = DriverAppValidation::subscriptionPaymentValidation($request);

        if($validate['status'] == "error"){
            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $subscription = Subscription::find($request->id);

        $driver = auth()->user();

        $transaction = new Transaction();
        $transaction->driver_id = $driver->id;
        $transaction->subscription_id = $subscription->id;
        $transaction->amount = $subscription->amount;
        $transaction->save();

        $order_id = TransactionSave::dispatch($transaction->id);

        return response()->json(['status' => 'success', 'message' => 'Transansaction Save Successfully', 'order_id' => $order_id[0]],200);
    }

    public function transactionStatus(Request $request){

        $validate = DriverAppValidation::transactionStatusValidation($request);

        if($validate['status'] == "error"){
            return response()->json(['status' => 'error','message' => $validate['message']],400);
        }
        $driver = auth()->user();

        $transaction = Transaction::where('rz_order_id',$request->order_id)->where('driver_id',$driver->id)->first();

        if(!$transaction){

            return response()->json(['status' => 'error', 'message' => 'Order Id Does Not Matches!'],400);
        }

        $transaction->status = $request->status;

        $transaction->save();

        if($request->status == 1 && $request->type == "Subscription"){//success

            DriverSubscriptionSave::dispatch($transaction);

        }elseif($request->status == 1 && $request->type == "PayTax"){

            DriverPayTaxSave::dispatch($transaction);
        }

        return response()->json(['status' => 'success', 'message' => 'Transansaction Paid Status Updated Successfully'],200);
    }

}
