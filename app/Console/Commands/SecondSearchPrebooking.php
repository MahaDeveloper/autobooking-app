<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SearchRide;
use App\Http\Controllers\Frontend\User\PreBookingController;
use Carbon\Carbon;
use DB;

class SecondSearchPrebooking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'second:search';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prebooking Second Search';

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

                $prebooking_controller->secondSearchPrebooking($booking->id);

                $prebooking_controller->thirdSearchPrebooking($booking->id);
            }
        }
        return 0;
    }
}
