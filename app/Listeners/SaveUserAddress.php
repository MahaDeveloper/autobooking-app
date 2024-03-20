<?php

namespace App\Listeners;

use App\Events\UserAddressSave;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\UserAddress;
use App\Helpers\MultipleLanguage;

class SaveUserAddress
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
     * @param  \App\Events\UserAddressSave  $event
     * @return void
     */
    public function handle(UserAddressSave $event)
    {
        $request = $event->request;
        $user_id = $event->user_id;

        if($request->user_address){

            foreach($request->user_address as $address){

                if($address['id']){

                    $user_address = UserAddress::find($address['id']);
                }else{
                    $exist_address_type = UserAddress::where('type',$address['type'])->first();

                    if($exist_address_type){
                        return "type-exist";

                    }else{
                        $user_address = new UserAddress();
                        $user_address->user_id = $user_id;
                    }
                }
                $user_address->latitude = $address['latitude'];
                $user_address->longitude = $address['longitude'];
                $user_address->address = $address['address'];

                $language_address = MultipleLanguage::allLanguages($address['address']);
                $user_address->languages_address = json_encode($language_address);

                $user_address->pin_code = $address['pin_code'];
                $user_address->type = $address['type'];

                $user_address->save();
            }

        }

    }
}
