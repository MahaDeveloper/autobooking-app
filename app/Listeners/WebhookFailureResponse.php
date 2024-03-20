<?php

namespace App\Listeners;

use App\Events\WebhookFailure;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Transaction;

class WebhookFailureResponse
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
     * @param  \App\Events\WebhookFailure  $event
     * @return void
     */
    public function handle(WebhookFailure $event)
    {
        $response = $event->response;

        $order_id = $event->all_ids['order_id'];

        $transaction_id = $event->all_ids['transaction_id'];

        $event_id = $event->all_ids['event_id'];

        switch ($response['method']){
 
            case "card": 
    
              $paymentinfo = ['status' => "failure",'amount' => $response['amount'],'currency' => $response['currency'],'order_id' => $response['order_id'],'method' => $response['method'],'razorpay_id' => $response['id'],'email' => $response['email'],'contact' => $response['contact'],'account_id' => $account_id,'captured' => $response['captured'],'card_id' => $response['card_id'],'card_details' => $response['card'],'error_code' => $response['error_code'],'error_description' => $response['error_description'],'error_source' => $response['error_source'],'error_step' => $response['error_step'],'error_reason' => $response['error_reason']];
    
            break;
    
            case "wallet":
    
               $paymentinfo = ['status' => "failure",'amount' => $response['amount'],'currency' => $response['currency'],'order_id' => $response['order_id'],'method' => $response['method'],'razorpay_id' => $response['id'],'email' => $response['email'],'contact' => $response['contact'],'account_id' => $account_id,'captured' => $response['captured'],'wallet' => $response['wallet'],'error_code' => $response['error_code'],'error_description' => $response['error_description'],'error_source' => $response['error_source'],'error_step' => $response['error_step'],'error_reason' => $response['error_reason']];
    
            break;
    
            case "upi":
    
               $paymentinfo = ['status' => "failure",'amount' => $response['amount'],'currency' => $response['currency'],'order_id' => $response['order_id'],'method' => $response['method'],'razorpay_id' => $response['id'],'email' => $response['email'],'contact' => $response['contact'],'account_id' => $account_id,'captured' => $response['captured'],'error_code' => $response['error_code'],'error_description' => $response['error_description'],'error_source' => $response['error_source'],'error_step' => $response['error_step'],'error_reason' => $response['error_reason']];
    
            break;
    
            default:
    
                $paymentinfo = ['status' => "failure",'amount' => $response['amount'],'currency' => $response['currency'],'order_id' => $response['order_id'],'method' => $response['method'],'bank' => $response['bank'],'razorpay_id' => $response['id'],'email' => $response['email'],'contact' => $response['contact'],'account_id' => $account_id,'captured' => $response->captured,'error_code' => $response['error_code'],'error_description' => $response['error_description'],'error_source' => $response['error_source'],'error_step' => $response['error_step'],'error_reason' => $response['error_reason']];
    
        }

        $transaction = Transaction::where('rz_order_id',$order_id)->whereNull('rz_event_id')->find($transaction_id);

        if($transaction){

            $transaction->webhook_status = 2;//failure
            $transaction->payment_details = $paymentinfo;
            $transaction->rz_event_id = $event_id;
            $transaction->status = 3;//failure
            $transaction->save();
        }
    }
}
