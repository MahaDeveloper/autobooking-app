<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Models\AdminNotification;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminNotificationResource;


class NotificationController extends Controller
{
    public function readstatus(Request $request){

        $notification_count = AdminNotification::orderBy('created_at','desc')->get();

        $notifications = AdminNotification::orderBy('created_at','desc')->whereJsonContains('viewed_admins',request()->user()->id)->get();

        if(count($notification_count) == count($notifications)){

            $read_status = 0;
        }else{
            $read_status = 1;
        }
        return response()->json(['status' => 'success','read_status' => $read_status],200);

    }

    public function readEntry(Request $request){

        $notifications = AdminNotification::orderBy('created_at','desc')->get();

        foreach($notifications as $notify){

            if($notify->viewed_admins == null){
                $set[] = $request->user()->id;
            }else{
                 $set = json_decode($notify->viewed_admins);

                $set_of = array_push($set,$request->user()->id);
            }

            $notify->viewed_admins = $set;
            $notify->save();
        }

        return response()->json(['status' => 'success','message' => "Read status updated successfully"],200);
    }

    public function currentNotificationAdmin(Request $request){

        $notifications = AdminNotificationResource::collection(AdminNotification::orderBy('created_at','desc')->limit(10)->get());

        return response()->json(['status' => 'success','notifications' => $notifications],200);
    }

    public function allNotification(Request $request){

        $notifications = AdminNotificationResource::collection(AdminNotification::orderBy('created_at','desc')->get());

        return response()->json(['status' => 'success','notifications' => $notifications],200);
    }
}
