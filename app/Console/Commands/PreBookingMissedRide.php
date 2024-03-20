<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SearchRide;
use App\Models\User;
use App\Events\DriverUserNotificationEvent;
use Carbon\Carbon;

class PreBookingMissedRide extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prebooking:missed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change status for Missed Prebooking Ride';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $missed_rides = SearchRide::where('status',4)->get();
        $currentDateTime = Carbon::now()->subMinutes(2)->setSeconds(0)->format('Y-m-d H:i:s');

        foreach($missed_rides as $ride){

            if(($ride->status == 4 && ($ride->prebooking_time == $currentDateTime))) {

                if($ride->ride_type == 2){
                    $ride->status = 3; //ride canceled

                }else{
                    $ride->status = 1 ;//driver not allocate
                }

                $ride->save();

                $user = User::find($ride->user_id);

                $msg = "Hi ".$user->name.",This Ride Has Been Canceled Due to Currently Driver Not Available, Search Ride Again";

                DriverUserNotificationEvent::dispatch($user, null, 'AutoKaar', $msg, 'driver-not-allocate','mrautokaar');
            }
        }

        return 0;
    }
}
