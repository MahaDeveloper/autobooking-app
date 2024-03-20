<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DriverSubscription;
use App\Models\Driver;
use App\Events\DriverUserNotificationEvent;
use App\Events\SendSmsEvent;
use Carbon\Carbon;

class SubscriptionEndAlertCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:alert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscription End Coming Soon';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $subscription_alerts = DriverSubscription::where('end_date','>=',today()->addDays(3))->get();

        foreach($subscription_alerts as $subscription){

            if(Carbon::parse($subscription->end_date)->toDateString() == Carbon::now()->addDays(3)->toDateString()){

                $driver = Driver::find($subscription->driver_id);

                $msg = "Hi ".$driver->name.", your subscription plan is going to expire by ".$subscription->end_date.".

                To keep your account active, we request you purchase an ATOKAR subscription.

                For support, please contact our support team (1800-123-6848).";
            }
            elseif(Carbon::parse($subscription->end_date)->toDateString() == Carbon::now()->addDays(2)->toDateString()){

                $driver = Driver::find($subscription->driver_id);

                $msg = "Hi ".$driver->name.", your subscription plan is going to expire by ".$subscription->end_date.".

                To keep your account active, we request you purchase an ATOKAR subscription.

                For support, please contact our support team (1800-123-6848).";
            }
            elseif(Carbon::parse($subscription->end_date)->toDateString() == Carbon::now()->addDays(1)->toDateString()){

                $driver = Driver::find($subscription->driver_id);

                $msg = "Hi ".$driver->name.", your subscription plan is going to expire by ".$subscription->end_date.".

                To keep your account active, we request you purchase an ATOKAR subscription.

                For support, please contact our support team (1800-123-6848).";
            }

        }

        if($driver){
            DriverUserNotificationEvent::dispatch($driver, null, 'AutoKaar', $msg,null,'mrautokaar');

            if(env('APP_ENV') != 'localhost'){

                SendSmsEvent::dispatch('1207168122092214423',$driver->mobile, $msg);
            }
        }
        return 0;
    }
}
