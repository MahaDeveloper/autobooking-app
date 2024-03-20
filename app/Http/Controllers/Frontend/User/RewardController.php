<?php

namespace App\Http\Controllers\Frontend\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserReward;
use App\Helpers\UserAppValidation;

class RewardController extends Controller
{
    public function userGifts(Request $request){

        $user = auth()->user();

        $total_scrached_amt = UserReward::where('user_id',$user->id)->where('status',1)->sum('reward_amount');

        $total_earned_amt = UserReward::where('user_id',$user->id)->where('status',4)->sum('reward_amount');

        $scratch_cards = UserReward::where('user_id',$request->user()->id)->get();

        return response()->json(['status' => 'success', 'total_earned_amt' =>$total_earned_amt ,'total_scrached_amt' => $total_scrached_amt, 'scratch_cards' => $scratch_cards, ],200);
    }

    public function scratchGift(Request $request){

        $validate = UserAppValidation::scratchGiftValidation($request);

        if ($validate['status'] == "error") {
            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        $reward = UserReward::find($request->user_reward_id);

        if($reward->status == 1){

            return response()->json(['status' => 'error','message' => "It is already scratched !"],400);
        }

        if($reward->end_date == today()){

            $reward->status = 2;//expired
            $reward->save();

            return response()->json(['status' => 'error','message' => "Sorry The Gift Coupon is Expired !"],400);
        }

        $reward->status = 1; //scratched
        $reward->save();

        return response()->json(['status' => 'success','message' => "The Coupon Scratched Successfully"],200);
    }

    public function requestReward(Request $request){

        $validate = UserAppValidation::requestRewardValidation($request);

        if ($validate['status'] == "error") {
            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }
        $user = auth()->user();

        $user_rewards = UserReward::where('user_id',$user->id)->where('status',1)->get();//scratched

        $reward_amt = UserReward::where('user_id',$user->id)->where('status',1)->sum('reward_amount');

        if($request->amount < 100){

            return response()->json(['status' => 'error', 'message' => 'Request Reward Amount Should Not Less than 100 Rs.'],400);
        }

        if($request->amount <= $reward_amt){

            foreach($user_rewards as $reward){

                $reward->status = 3; //requested

                $reward->save();
            }

            $user->upi_id = $request->upi_id;

            $user->save();

            return response()->json(['status' => 'success','message' => "The Reward Request sent Successfully"],200);

        }else{

            return response()->json(['status' => 'error', 'message' => 'Requested Amount Less Than Reward Earned Amount'],400);
        }
    }
}
