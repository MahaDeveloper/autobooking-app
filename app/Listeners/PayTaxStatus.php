<?php

namespace App\Listeners;

use App\Events\DriverPayTaxSave;
use App\Events\DriverUserNotificationEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\DriverPayment;
use App\Models\Driver;
use Log;

class PayTaxStatus
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
     * @param  \App\Events\DriverPayTaxSave  $event
     * @return void
     */
    public function handle(DriverPayTaxSave $event)
    {
        $current_pay_tax = DriverPayment::where('status',2)->where('driver_id',$event->transaction->driver_id)->first();
        $current_pay_tax->transaction_id = $event->transaction->id;
        $current_pay_tax->status = 3;//paid
        $current_pay_tax->save();

        $driver = Driver::find($event->transaction->driver_id);

        $driver->current_status = 2;//back to offline
        $driver->save();

        if($driver->current_status == 2){

            $mesg = "Hi ".$driver->name.", we are glad to inform your account has been re-activated successfully.
            Go-Online and start taking the rides!";

            DriverUserNotificationEvent::dispatch($driver,null,'AutoKaar',$mesg,null,'mrautokaar');
        }
    }
}

