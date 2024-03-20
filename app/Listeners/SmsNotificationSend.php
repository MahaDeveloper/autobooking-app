<?php

namespace App\Listeners;

use App\Events\SendSmsEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Log;

class SmsNotificationSend
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
     * @param  \App\Events\SendSmsEvent  $event
     * @return void
     */
    public function handle(SendSmsEvent $event)
    {
        $api_key = env('SMS_API_KEY');
        $url = env('SMS_URL');
        $sender_id = env('SENDER_ID');

        $data  = Http::get($url, [
            "api-key" => $api_key,
            "sender-id" => $sender_id,
            "sms-type" => 1,
            "mobile" => $event->mobile,
            "te_id" => $event->type,
            "message" => $event->message,
        ]);

    }
}
