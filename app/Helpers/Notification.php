<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Log;

class Notification{

    public static function sendNotification($user,$title,$ride_id,$description,$type,$channel_id){
        $serverkey ="AAAAl1sdfoM:APA91bH_Z6fQX0NXaDk8yTafNyx_5ZmBJe8kzzY310rw0rUy4iR35grncbW_4SoWmD1ZQYu9Lzc9zXi9fb609JJj2HP80xneQcc0USXdSt6KCq1jE1bipImyJZHcG7Gh6v8vNhKIQSfA";

        $firebase_id =  $user->fcm_id;

        if($firebase_id){

            $message =  [
                'body' => $description,
                'title' => $title,
                'android_channel_id' => $channel_id
            ];
            $data = [
                'body' => $description,
                'title' => $title,
                'sound' => 'default',
                'type' => $type ?? 'no',
                'ride_id' => $ride_id ?? 'no',
                'android_channel_id ' => $channel_id,
            ];
            $fields = [
                'to'=> $firebase_id,
                'notification'=> $message,
                'data' => $data,
                'sound' => 'default',
            ];
            $headers = ['Authorization: key='.$serverkey,'Content-Type: application/json'];
            $ch = curl_init();
            curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt( $ch,CURLOPT_POST, true);
            curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
            curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, true );
            curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
            curl_close($ch);

            return $result;
        }
        else{
            Log::info('no-fcm-id');
        }
    }

}

