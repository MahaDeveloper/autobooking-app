<?php

namespace App\Listeners;

use App\Events\WebhookSuccess;
use App\Events\DriverSubscriptionSave;
use App\Events\DriverPayTaxSave;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Transaction;

class WebhookSuccessResponse
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
     * @param  \App\Events\WebhookSuccess  $event
     * @return void
     */
    public function handle(WebhookSuccess $event)
    {
        $response = $event->response;

        $order_id = $event->all_ids['order_id'];

        $transaction_id = $event->all_ids['transaction_id'];

        $event_id = $event->all_ids['event_id'];

        $account_id = $event->all_ids['account_id'];

        switch ($response['method']){

            case "card":

                $paymentinfo = ['status' => "success",'amount' => $response['amount'],'currency' => $response['currency'],'order_id' => $response['order_id'],'method' => $response['method'],'razorpay_id' => $response['id'],'email' => $response['email'],'contact' => $response['contact'],'auth_code' => $response['acquirer_data']['auth_code'],'account_id' => $account_id,'captured' => $response['captured'],'card_id' => $response['card_id'],'card_details' => $response['card']];

            break;

            case "wallet":

                $paymentinfo = ['status' => "success",'amount' => $response['amount'],'currency' => $response['currency'],'order_id' => $response['order_id'],'method' => $response['method'],'razorpay_id' => $response['id'],'email' => $response['email'],'contact' => $response['contact'],'bank_trans_id' => $response['acquirer_data']['transaction_id'],'account_id' => $account_id,'captured' => $response['captured'],'wallet' => $response['wallet']];

            break;

            case "upi":

                $paymentinfo = ['status' => "success",'amount' => $response['amount'],'currency' => $response['currency'],'order_id' => $response['order_id'],'method' => $response['method'],'razorpay_id' => $response['id'],'email' => $response['email'],'contact' => $response['contact'],'rrn' => $response['acquirer_data']['rrn'],'account_id' => $account_id,'captured' => $response['captured'],'vpa' => $response['vpa']];

            break;

            default:

                $paymentinfo = ['status' => "success",'amount' => $response['amount'],'currency' => $response['currency'],'order_id' => $response['order_id'],'method' => $response['method'],'bank' => $response['bank'],'razorpay_id' => $response['id'],'email' => $response['email'],'contact' => $response['contact'],'bank_trans_id' => $response['acquirer_data']['bank_transaction_id'],'account_id' => $account_id,'captured' => $response['captured']];

        }


        $transaction = Transaction::where('rz_order_id',$order_id)->whereNull('rz_event_id')->find($transaction_id);

        if(($transaction->status == 0 || $transaction->status == 2) && $transaction->subscription_id){

            DriverSubscriptionSave::dispatch($transaction);

        }elseif(($transaction->status == 0 || $transaction->status == 2) && $transaction->subscription_id == null){

            DriverPayTaxSave::dispatch($transaction);
        }

        if($transaction){

            $transaction->webhook_status = 1;//success
            $transaction->payment_details = $paymentinfo;
            $transaction->rz_event_id = $event_id;
            $transaction->status = 2;//success
            $transaction->save();
        }
    }
}
