<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserReward;
use App\Models\Ride;
use App\Helpers\BackendValidation;
use DB;

class RewardController extends Controller
{
    public function rewardRequestList(Request $request){

        $request_rewards = UserReward::where('status',3)->with('user:id,name,image,email,upi_id,mobile')->when($request->start_date,function($query,$start)
        {
        $query->whereDate('created_at','>=',$start);

        })->when($request->end_date,function($query,$to){

        $query->whereDate('created_at','<=',$to);

        })->groupBy('user_id')->select('*',DB::raw('SUM(user_rewards.reward_amount) as request_reward_amt'), DB::raw('SUM(user_rewards.ride_amount) as total_ride_amt'))->get(); //3->requested

        $credited_rewards = UserReward::where('status',4)->with('user:id,name,image,email,upi_id,mobile')->when($request->start_date,function($query,$start)
        {
        $query->whereDate('created_at','>=',$start);

        })->when($request->end_date,function($query,$to){

        $query->whereDate('created_at','<=',$to);

        })->groupBy('user_id')->select('*',DB::raw('SUM(user_rewards.reward_amount) as request_reward_amt'), DB::raw('SUM(user_rewards.ride_amount) as total_ride_amt'))->get(); //4->credited

        return response()->json(['status' => 'success','request_rewards' => $request_rewards, 'credited_rewards' => $credited_rewards],200);
    }

    public function rewardRequestDetail(Request $request){

        $validate = BackendValidation::rewardRequestDetailValidation($request);

        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        $reward_detail = UserReward::with('user:id,name,image,email,upi_id,mobile')->where('user_id',$request->user_id)->groupBy('user_id')->get();

        $total_reward_offered = UserReward::where('status',0)->where('user_id',$reward_detail[0]->user_id)->sum('reward_amount');

        $total_reward_expired = UserReward::where('status',2)->where('user_id',$reward_detail[0]->user_id)->sum('reward_amount');

        $reward_withdrawn = UserReward::where('status',4)->where('user_id',$reward_detail[0]->user_id)->sum('reward_amount'); //credited

        $withdrawn_request = UserReward::where('status',3)->where('user_id',$reward_detail[0]->user_id)->sum('reward_amount'); //requested

        return response()->json(['status' => 'success','total_reward_offered' => $total_reward_offered, 'total_reward_expired' => $total_reward_expired, 'reward_withdrawn' => $reward_withdrawn, 'withdrawn_request' => $withdrawn_request,'reward_detail' => $reward_detail],200);
    }

    public function rewardHistory(Request $request){

        $validate = BackendValidation::rewardHistoryValidation($request);
        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        $user_rewards = UserReward::where('user_id',$request->user_id)->where('status',4)->get(); //gifted

        return response()->json(['status' => 'success','user_rewards' => $user_rewards],200);
    }

    public function paidReward(Request $request){

        $validate = BackendValidation::rewardRequestDetailValidation($request);
        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        $request_rewards = UserReward::where('user_id',$request->user_id)->where('status',3)->get();

        foreach($request_rewards as $reward){

            $reward->status = 4; //creted

            $reward->save();
        }

        return response()->json(['status' => 'success','message' => 'Reward Amount Paid Successfully'],200);
    }


}
