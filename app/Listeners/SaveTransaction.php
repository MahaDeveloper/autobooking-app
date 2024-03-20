<?php

namespace App\Listeners;

use App\Events\TransactionSave;
use App\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Razorpay\Api\Api;
use Log;

class SaveTransaction
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
     * @param  \App\Events\TransactionSave  $event
     * @return void
     */
    public function handle(TransactionSave $event)
    {
        $transaction_id = $event->transaction_id;

        $transaction  = Transaction::find($transaction_id);
        $api = new Api(env('RAZORPAY_TEST_KEY'), env('RAZORPAY_TEST_SECRET'));

        $order = $api->order->create([
            'receipt' => 'order_receipt'.rand(1000,9999),
            'amount'  => $transaction->amount.'00',
            'currency' => 'INR',
        ]);

        $transaction->rz_order_id = $order->id;
        $transaction->save();

        return $order->id;

    }
}
