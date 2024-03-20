<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Driver;
use App\Events\DriverUserNotificationEvent;
use Carbon\Carbon;

class TakeRideCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'take:ride';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Go Online, Start Taking Rie';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $currentDateTime = Carbon::now()->addHours(9)->setSeconds(0)->format('Y-m-d H:i:s');
        $drivers = Driver::where('verification_status',2)->whereNull('checkin_time')->where('accepted_rejected_date','=',$currentDateTime)->get();

        foreach($drivers as $driver){

            $msg = "Hi ".$driver->name.", please Go-Online and start taking rides.";

            DriverUserNotificationEvent::dispatch($driver, null, 'AutoKaar', $msg,null,'mrautokaar');
        }
        return 0;
    }
}
