<?php

namespace App\Listeners;

use App\Events\GiftReward;
use App\Events\DriverUserNotificationEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Carbon\Carbon;
use App\Models\Ride;
use App\Models\User;
use App\Models\Reward;
use App\Models\UserReward;
use Log;

class UserPresentGift
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
     * @param  \App\Events\GiftReward  $event
     * @return void
     */
    public function handle(GiftReward $event)
    {
        $user_id = $event->ride->user_id;

        $rides_amount = Ride::where('user_id',$user_id)->whereIn('status',[10,12])->sum('final_amount');

        $reward_check = Reward::where('ride_amount','=',$rides_amount)->latest()->first();

        $user = User::find($user_id);
        $msg = "Hi ".$user->name.", You Have Received a New Reward For Your Current Ride!!.";

        if($reward_check){
            $new_user_reward = new UserReward();
            $new_user_reward->ride_amount = $event->ride->final_amount;
            $new_user_reward->user_id = $user_id;
            $new_user_reward->ride_id = $event->ride->id;
            $new_user_reward->reward_amount = $reward_check->reward_amount;
            $new_user_reward->end_date = Carbon::today()->addDays($reward_check->validity);
            $new_user_reward->status = 0;//gifted
            $new_user_reward->save();

            DriverUserNotificationEvent::dispatch($user, $event->ride->id, 'AutoKaar', $msg,null,'mrautokaar');

            $user->reward_count = 3;
        }else{

            if($user->reward_count == 0){

                $new_user_reward = new UserReward();
                $new_user_reward->ride_amount = $event->ride->final_amount;
                $new_user_reward->user_id = $user_id;
                $new_user_reward->ride_id = $event->ride->id;
                $new_user_reward->reward_amount = 0;
                $new_user_reward->status = 0;//gifted
                $new_user_reward->save();

                DriverUserNotificationEvent::dispatch($user, $event->ride->id, 'AutoKaar', $msg,null,'mrautokaar');

                $user->reward_count = null;

            }elseif($user->reward_count == null){

                $user->reward_count = 3;
            }else{

                $user->reward_count -= 1;
            }
        }

        $user->save();

    }
}
