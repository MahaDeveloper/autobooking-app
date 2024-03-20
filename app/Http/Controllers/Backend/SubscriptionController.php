<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Helpers\BackendValidation;
use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Models\DriverSubscription;
use App\Models\Driver;
use App\Models\Transaction;
use App\Helpers\MultipleLanguage;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $subscriptions = SubscriptionResource::collection(Subscription::all());

        return response()->json(['status' => 'success', 'subscriptions' => $subscriptions], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validate = BackendValidation::subscriptionValidation($request,$id=null);

        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        $subscription = new Subscription();
        $subscription->name = $request->name;

        $names = MultipleLanguage::allLanguages($request->name);
        $subscription->languages_name = json_encode($names);

        $subscription->amount = $request->amount;
        $subscription->validity = $request->validity;

        $subscription->save();

        return response()->json(['status' => 'success','message' => "The subscription Added Successfully"],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $subscription = new SubscriptionResource(Subscription::findOrFail($id));

        $driver_subscriptions = DriverSubscription::with('driver:id,name,image,mobile')->where('subscription_id',$id)->where('status',1)->when($request->start_date,function($query,$start)
        {
           $query->whereDate('created_at','>=',$start);

        })->when($request->end_date,function($query,$to){

           $query->whereDate('created_at','<=',$to);

        })->get();

        return response()->json(['status' => 'success', 'subscription' => $subscription , 'driver_subscriptions' => $driver_subscriptions], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $subscription = new SubscriptionResource(Subscription::findOrFail($id));

        $driver_subscriptions = DriverSubscription::with('driver:id,name,image')->where('subscription_id',$id)->where('status',1)->get();

        return response()->json(['status' => 'success', 'subscription' => $subscription, 'driver_subscriptions' => $driver_subscriptions], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validate = BackendValidation::subscriptionValidation($request,$id);

        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        $subscription = Subscription::find($id);
        $subscription->name = $request->name;

        $names = MultipleLanguage::allLanguages($request->name);
        $subscription->languages_name = json_encode($names);

        $subscription->amount = $request->amount;
        $subscription->validity = $request->validity;

        $subscription->save();

        return response()->json(['status' => 'success','message' => "The subscription updated Successfully"],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $subscription = Subscription::find($id);

        $subscription->delete();

        return response()->json(['status' => 'success', 'message' => "The subscription Has Been Deleted"], 200);
    }

    public function status($id)
    {
        $subscription = Subscription::find($id);

        if ($subscription->status) {
            $subscription->status = 0;
            $msg = "The subscription Has Been De Activated";
        } else {
            $subscription->status = 1;
            $msg = "The subscription Has Been Activated";
        }

        $subscription->save();

        return response()->json(['status' => 'success', 'message' => $msg], 200);
    }

    public function changeSubscriptionEndDate(Request $request){

        $validate = BackendValidation::changeSubscriptionEndDateValidation($request);

        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        $driver = Driver::find($request->driver_id);

        $driver->subscription_end_date = $request->subscription_end_date;

        $driver->save();

        $driver_subscription = DriverSubscription::where('driver_id',$request->driver_id)->latest()->first();
        $driver_subscription->end_date = $request->subscription_end_date;
        $driver_subscription->save();

        return response()->json(['status' => 'success','message' => "The Subscription End Date Updated Successfully"],200);
    }

    public function SubscriptionPaymentDetails(Request $request){

        $validate = BackendValidation::SubscriptionPaymentDetailsValidation($request);

        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        $payment_detail = Transaction::with('subscription')->where('driver_id',$request->driver_id)->where('subscription_id',$request->subscription_id)->latest()->first();

        return response()->json(['status' => 'success','payment_detail' => $payment_detail ?? null ],200);
    }

}
