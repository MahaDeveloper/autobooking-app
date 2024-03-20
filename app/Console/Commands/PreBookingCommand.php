<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SearchRide;
use App\Http\Controllers\Frontend\User\PreBookingController;
use Carbon\Carbon;
use DB;

class PreBookingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pre:booked';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send response for prebooked ride';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $currentDateTime = Carbon::now()->addMinutes(3)->setSeconds(0)->format('Y-m-d H:i:s');
        $pre_bookings = SearchRide::where('status',4)->where('prebooking_time','=', $currentDateTime)->get();

        foreach ($pre_bookings as $booking) {

            if($booking->status != 2){ //driver not allocate

                $prebooking_controller = new PreBookingController();

                $prebooking_controller->firstSearchPrebooking($booking->id);
            }
        }
        
        return 0;
    }
}

