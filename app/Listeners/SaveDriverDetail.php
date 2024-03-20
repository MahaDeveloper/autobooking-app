<?php

namespace App\Listeners;

use App\Events\DriverDetailSave;
use App\Events\DriverProofSave;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\DriverDetail;
use Illuminate\Support\Facades\Storage;
use App\Helpers\MultipleLanguage;

class SaveDriverDetail
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
     * @param  \App\Events\DriverDetailSave  $event
     * @return void
     */
    public function handle(DriverDetailSave $event)
    {
        $request = $event->request;
        $driver_id = $event->driver_id;

        $driver_detail = DriverDetail::where('driver_id',$event->driver_id)->first();

        if(!$driver_detail){

            $driver_detail = new DriverDetail();
            $driver_detail->driver_id = $driver_id;
        }

        $driver_detail->email = $request->email;
        $driver_detail->address = $request->address;
        $language_address = MultipleLanguage::allLanguages($request->address);
        $driver_detail->languages_address = json_encode($language_address);
        $driver_detail->upi_id = $request->upi_id;
        $driver_detail->upi_number = $request->upi_number;

        if($request->hasFile('qr_code')){
            $name = Storage::disk('digitalocean')->putFile('driver_images',$request->qr_code,'public');
            $driver_detail->qr_code = $name;
        }

        $driver_detail->save();

    }
}
