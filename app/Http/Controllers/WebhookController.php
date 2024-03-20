<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\WebhookSignature;
use App\Events\WebhookFailure;
use App\Events\WebhookSuccess;

class WebhookController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $verify = WebhookSignature::dispatch($request);

        if($verify == "failure"){
            return;
        }

        $event_id = $request->header('x-razorpay-event-id');//event id

        $payment_response = $request;

        $payment_payload =  $payment_response->payload;

        $payment_payment = $payment_payload['payment'];

        $payment_entity =  $payment_payment['entity'];

        $payment_notes = $payment_entity['notes'];

        $account_id = $payment_response->account_id;

        $transaction_id =  $payment_notes['transaction_id']; // transaction id

        $response = $payment_entity;

        $order_id = $response['order_id']; //razorpay order id

        $all_ids = ['event_id' => $event_id,'transaction_id' => $transaction_id,'order_id' => $order_id,'account_id' => $account_id];

        if($payment_response->event == "payment.failed"){

            WebhookFailure::dispatch($all_ids,$response);

        }elseif($payment_response->event == "payment.captured"){

            WebhookSuccess::dispatch($all_ids,$response);
        }
    }
}
