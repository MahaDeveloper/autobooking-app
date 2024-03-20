<?php

namespace App\Services;

use App\Interfaces\SupportInterface;
use App\Models\Support;
use App\Models\Driver;
use App\Models\User;
use App\Events\AdminNotificationSend;
use App\Events\DriverUserNotificationEvent;
use Log;

class SupportService implements SupportInterface
{
    public function supportStore($request): string{

        $user = auth()->user();

        $support = new Support();
        $support->supportable()->associate($user);
        $support->description = $request->description;
        $support->ride_id = $request->ride_id;
        $support->save();

        $table = $user->getTable();

        $msg = "Hi ".$user->name.", thank you for reaching our support team.

        Your Ticket ID: AK00".$support->id."
        Ticket Status: Open

        In general, we offer resolution within 2 business days. We do appreciate your patience.";

        DriverUserNotificationEvent::dispatch($user, null, 'AutoKaar', $msg,null,'mrautokaar');

        $title = "Support Raised !";

        if($table == "users"){

            $description = "The Passenger $user->name raised support.";

        }else{

            $description = "The Driver $user->name raised support.";

        }

        AdminNotificationSend::dispatch($title,$description,$request->ride_id);

        return "success";

    }

    public function supportReply($request): string{

        $admin = auth()->user();

        $support = Support::find($request->support_id);
        $support->reply_msg = $request->reply_msg;
        $support->status = 1;//replied
        $support->admin_id = $admin->id;
        $support->save();

        if($support->supportable_type == 'App\Models\User'){

            $user = User::find($request->support_id);

        }elseif($support->supportable_type == 'App\Models\Driver'){

            $user = Driver::find($request->support_id);
        }

        $msg = "Hi ".$user->name.", thank you for reaching our support team.

        Your Ticket ID: AK00".$support->id."
        Ticket Status: Closed

        In general, we offer resolution within 2 business days. We do appreciate your patience.";

        $message = "Hi ".$user->name.", we believe your support ticket has been resolved. You can always contact us back by visiting our website.

        Please click on the below link and rate the service offered by our customer support team.";

        DriverUserNotificationEvent::dispatch($user, null, 'AutoKaar', $msg,null,'mrautokaar');

        if($message){
            DriverUserNotificationEvent::dispatch($user, null, 'AutoKaar', $message,null,'mrautokaar');
        }
        return "success";
    }
}
