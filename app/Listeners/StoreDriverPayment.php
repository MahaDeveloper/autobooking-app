<?php

namespace App\Listeners;

use App\Events\DriverPaymentStore;
use App\Events\SendSmsEvent;
use App\Events\DriverUserNotificationEvent;
use App\Models\DriverPayment;
use App\Models\Driver;
use App\Models\Setting;
use App\Models\RideBillingDetail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Log;

class StoreDriverPayment
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\DriverPaymentStore  $event
     * @return void
     */
    public function handle(DriverPaymentStore $event)
    {
        $ride = $event->ride;

        $setting = Setting::where('type',3)->first();//reached amount

        $final_tax = RideBillingDetail::where('ride_id',$ride->id)->selectRaw('SUM(fare_details->>"$.final_tax") as final_tax')->pluck('final_tax')->first();

        if($final_tax == 0){

            $final_tax = RideBillingDetail::where('ride_id',$ride->id)->selectRaw('SUM(fare_details->>"$.estimated_tax") as estimated_tax')->pluck('estimated_tax')->first();
        }

        $driver_payment = DriverPayment::where('driver_id',$ride->driver_id)->where('status',1)->first();//filled

        if($driver_payment){

            $exist_ride_ids = $driver_payment->pluck('ride_ids')->first();

            $ride_id_array = json_decode($exist_ride_ids, true) ?? [];

            if(!in_array($ride->id, $ride_id_array)){

                array_push($ride_id_array, $ride->id);
            }

            $json_ride_id = $ride_id_array;


            if(($driver_payment->amount + $final_tax) == $setting->value){
                log::info('vv');

                $driver_payment->status = 2; //reached

                $driver_payment->ride_ids = json_encode($json_ride_id);

                $driver_payment->amount = $driver_payment->amount + $final_tax;


            }elseif(($driver_payment->amount + $final_tax) > $setting->value){
                log::info('cc');

                $remain_amt = ($driver_payment->amount + $final_tax) - $setting->value;

                $driver_payment->amount = $setting->value;

                $driver_payment->status= 2; //reached

                if($remain_amt)
                $d_remain_payment = new DriverPayment();
                $array = $json_ride_id;
                $last_ride_id = end($array);
                $d_remain_payment->driver_id = $driver_payment->driver_id;
                $d_remain_payment->ride_ids = json_encode([$last_ride_id]);
                $d_remain_payment->amount = $remain_amt;
                $d_remain_payment->save();
            }else{

                $driver_payment->ride_ids = json_encode($json_ride_id);

                $driver_payment->amount = $driver_payment->amount + $final_tax;
            }
        }
        else{

            $driver_payment = new DriverPayment();

            $driver_payment->driver_id = $ride->driver_id;

            if($final_tax == $setting->value){

                $driver_payment->status = 2; //reached

                $driver_payment->amount = $final_tax;

            }elseif($final_tax > $setting->value){

                $remain_amt = $final_tax - $setting->value;

                $driver_payment->amount = $setting->value;

                $driver_payment->status = 2;

                if($remain_amt)
                $d_remain_payment = new DriverPayment();
                $d_remain_payment->driver_id = $ride->driver_id;
                $d_remain_payment->ride_ids = json_encode([$ride->id]);
                $d_remain_payment->amount = $remain_amt;
                $d_remain_payment->save();
            }
            else{

                $driver_payment->amount = $final_tax;
            }

            $driver_payment->ride_ids = json_encode([$ride->id]);
        }

        $driver_payment->save();

        $driver = Driver::find($driver_payment->driver_id);

        if($driver_payment->status == 2){

            $msg = "Hi ".$driver->name." , you have reached your tax limit of 150.00 INR.

            We request you to pay the tax payment to avoid any service interruption.

            Your account will be suspended if you do not pay the tax amount in next 12 hours.

            For any support, please contact our support team (1800-123-6848) ATOKAR .";

            DriverUserNotificationEvent::dispatch($driver, $ride->id, 'AutoKaar', $msg,null,'mrautokaar');

            if(env('APP_ENV') != 'localhost'){

                SendSmsEvent::dispatch('1207168122000195201',$driver->mobile, $msg);
            }
        } //reached


    }
}
