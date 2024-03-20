<?php

namespace App\Listeners;

use App\Events\WebhookSignature;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class VerifySignature
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
     * @param  \App\Events\WebhookSignature  $event
     * @return void
     */
    public function handle(WebhookSignature $event)
    {
        $request = $event->request;
    
        $event_id = $request->header('x-razorpay-event-id');//event id
    
        $webhookSignature = $request->header('x-razorpay-signature');//signature to check
    
        $webhookSecret = env('WEBHOOK_PASSWORD');
    
        $expected_signature = hash_hmac('sha256', $request->getContent(), $webhookSecret);
    
        if($expected_signature != $webhookSignature){
    
            Log::info('fake-webhook');
            return 'failure'; 
    
        }else{

            return 'success';

        }
    }
}
