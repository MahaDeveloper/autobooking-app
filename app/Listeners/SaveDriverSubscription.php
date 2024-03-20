<?php

namespace App\Listeners;

use App\Events\DriverSubscriptionSave;
use App\Events\DriverUserNotificationEvent;
use App\Events\SendSmsEvent;
use App\Models\Subscription;
use App\Models\DriverSubscription;
use App\Models\Driver;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Carbon\Carbon;
use Log;

class SaveDriverSubscription
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
     * @param  \App\Events\DriverSubscriptionSave  $event
     * @return void
     */
    public function handle(DriverSubscriptionSave $event)
    {
        $transaction = $event->transaction;

        $driver_subscription  = new DriverSubscription();

        $subscription = Subscription::find($transaction->subscription_id);

        $driver_subscription->driver_id = $transaction->driver_id;
        $driver_subscription->subscription_id = $transaction->subscription_id;
        $driver_subscription->transaction_id = $transaction->id;
        $driver_subscription->start_date = Carbon::today();
        $driver_subscription->end_date = Carbon::today()->addDays($subscription->validity);
        $driver_subscription->save();

        $driver = Driver::find($transaction->driver_id);
        $driver->subscription_end_date = $driver_subscription->end_date;
        $driver->current_status = 0; //active
        $driver->save();

        $title = "AutoKaar";

        $msg = "Hi ".$driver->name.", subscription was successfully activated on our Mr. AutoKaar Driver account.

        Your Subscription will end on ".$driver->subscription_end_date.".

        Please click on this link to view your subscription invoice:";

        DriverUserNotificationEvent::dispatch($driver, null, $title, $msg,null,'mrautokaar');

        if(env('APP_ENV') != 'localhost'){

            SendSmsEvent::dispatch('1207168122081841740',$driver->mobile, $msg);
        }

        $message = "Hi ".$driver->name.", we are glad to inform your account has been re-activated successfully.

        Go-Online and start taking the rides!";

        DriverUserNotificationEvent::dispatch($driver, null, $title, $message,null,'mrautokaar');

    }
}
