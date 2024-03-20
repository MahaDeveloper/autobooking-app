<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DriverPayment;
use App\Models\Driver;
use App\Events\DriverUserNotificationEvent;
use App\Events\SendSmsEvent;
use Carbon\Carbon;

class PayTaxCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pay:tax';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pay Tax Within 12 hours';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $currentDate = Carbon::today();
        $currentDateTime = Carbon::now()->addMinutes(1)->setSeconds(0)->format('Y-m-d H:i:s');
        $reached_pay_tax = DriverPayment::where('status',2)->where('updated_at', '=', $currentDate)->get();
        //2->reached, after 12 hours
       foreach($reached_pay_tax as $tax)
       {
            $driver = Driver::find($tax->driver_id);
            $driver->current_status = 4; //payment pending
            $driver->save();

            if($tax && $driver->updated_at->setSeconds(0) == $currentDateTime){
                $title = "AutoKaar";

                $msg = "Hi ".$driver->name.", we are sorry to inform your account has been suspended due to non payment of tax amount.
                You need to pay the tax amount of 150.00 INR to re-activate your account and Go-Online.
                For more information, please contact our support team (1800-123-6848) ATOKAR .";

                DriverUserNotificationEvent::dispatch($driver, null, $title, $msg,null,'mrautokaar');

                if(env('APP_ENV') != 'localhost'){

                    SendSmsEvent::dispatch('1207168122014808164',$driver->mobile, $msg);
                }
            }
       }

        return 0;
    }
}
