<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SearchRide;
use App\Models\User;
use App\Events\DriverUserNotificationEvent;
use Carbon\Carbon;

class MissedRideCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'missed:ride';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change Status for Missed Rides';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $currentDateTime = Carbon::now()->subMinutes(2)->setSeconds(0)->format('Y-m-d H:i:s');

        $missed_rides = SearchRide::where('status',0)->whereRaw("DATE_FORMAT(search_time, '%Y-%m-%d %H:%i:00') = ?", [$currentDateTime])->get();

        $missed_prebooking_rides = SearchRide::where('status',4)->whereRaw("DATE_FORMAT(prebooking_time, '%Y-%m-%d %H:%i:00') = ?", [$currentDateTime])->get();
        
        foreach($missed_rides as $ride){

            $ride->status = 3; //ride cancel
            $ride->save();

            $user = User::find($ride->user_id);

            $msg = "Hi ".$user->name.",This Ride Has Been Canceled Due to Currently Driver Not Available, Search Ride Again";

            DriverUserNotificationEvent::dispatch($user, null, 'AutoKaar', $msg, 'driver-not-allocate','mrautokaar');
        }

        foreach($missed_prebooking_rides as $ride){

            $ride->status = 3; //ride cancel
            $ride->save();

            $user = User::find($ride->user_id);

            $msg = "Hi ".$user->name.",This Ride Has Been Canceled Due to Currently Driver Not Available, Search Ride Again";

            DriverUserNotificationEvent::dispatch($user, null, 'AutoKaar', $msg, 'driver-not-allocate','mrautokaar');
        }

        return 0;
    }
}
