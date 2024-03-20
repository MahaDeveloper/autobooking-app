<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DriverSubscription;
use App\Models\Driver;
use App\Events\DriverUserNotificationEvent;
use App\Events\SendSmsEvent;
use Carbon\Carbon;
use Log;

class SubscriptionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:end';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscription Ended';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $end_subscriptions = DriverSubscription::get();

        foreach($end_subscriptions as $subs){

            if(Carbon::parse($subs->end_date)->toDateString() == Carbon::now()->toDateString()){

                Log::info('subscription-end-check-cron');

                $driver = Driver::find($subs->driver_id);

                $driver->current_status = 5; //subscription ended

                $driver->save();

                if($driver)

                $msg = "Hi ".$driver->name.", your subscription with Mr. AutoKaar Driver account was expired.
                To Go-Online and take rides, open Mr. AutoKaar application and purchase a new subscription plan.
                For any support, feel free to contact our support team (1800-123-6848).";

                DriverUserNotificationEvent::dispatch($driver, null, 'AutoKaar', $msg,null,'mrautokaar');

                if(env('APP_ENV') != 'localhost'){

                    $msg = "Hi ".$driver->name.", we need to inform your Mr AutoKaar Driver account was suspended due to no active subscription.

                    To Go-Online and start taking rides, open the Mr AutoKaar application and purchase a new subscription plan.

                    For any support, feel free to contact our support team (1800-123-6848).";

                    SendSmsEvent::dispatch('1207168130784584865',$driver->mobile, $msg);
                }
            }
        }
        return 0;
    }
}
