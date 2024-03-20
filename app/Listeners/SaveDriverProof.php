<?php

namespace App\Listeners;

use App\Events\DriverProofSave;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\DriverProof;
use Illuminate\Support\Facades\Storage;

class SaveDriverProof
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
     * @param  \App\Events\DriverProofSave  $event
     * @return void
     */
    public function handle(DriverProofSave $event)
    {
        $request = $event->request;
        $driver_id = $event->driver_id;

        foreach($request->proofs as $proof){

            if($proof['id']){

                $driver_proof = DriverProof::find($proof['id']);
            }else{
                $exist_proof = DriverProof::where('type',$proof['type'])->where('driver_id',$driver_id)->first();

                if(!$exist_proof){
                    $driver_proof = new DriverProof();
                    $driver_proof->driver_id = $driver_id;
                }else{
                    return "error";
                }
            }
            $driver_proof->number = $proof['number'];
            $driver_proof->type = $proof['type'];
            $driver_proof->details = $proof['details'];
            $driver_proof->verified = $proof['verified'];

            $name = Storage::disk('digitalocean')->putFile('driver_images',$proof['image'],'public');
            $driver_proof->image = $name;

            $driver_proof->save();
        }
    }
}
