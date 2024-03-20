<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Driver;
use App\Events\DriverUserNotificationEvent;
use Carbon\Carbon;

class FreeSubscriptionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'free:subscription';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Free Subscription Ended';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $drivers = Driver::get();

        foreach($drivers as $driver){

            if(Carbon::parse($driver->subscription_end_date)->toDateString() == Carbon::now()->toDateString()){

                $msg = "Hi ".$driver->name.", renew your subscription to get rides on Mr. AutoKaar application.
                Choose your preferred plan immediately. Subscription at Lowest Prices!";

                DriverUserNotificationEvent::dispatch($driver, null, 'AutoKaar', $msg,null,'mrautokaar');
            }
        }
        return 0;
    }
}
