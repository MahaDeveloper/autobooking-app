<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('set null');
            $table->integer('otp');
            $table->decimal('pickup_latitude',10,7);
            $table->decimal('pickup_longitude',10,7);
            $table->decimal('drop_latitude',10,7);
            $table->decimal('drop_longitude',10,7);
            $table->float('final_amount');
            $table->float('distance');
            $table->boolean('status')->default(1)->comment('1->driver allocated , 2->ride cancelled, 3->driver on the way, 4->driver reached to pickup, 5-> otp verified and ride started ,6->reached to drop location, 7 ->changed destination request, 8->change destination accepted , 9->change destination rejected, 10->ride completed and driver acknowledged , 11->change destination and ride completed');
            $table->dateTime('reached_pickup_time')->nullable();
            $table->dateTime('ride_started_time')->nullable();
            $table->boolean('ride_type')->default(1)->comment('1->ride now, 2->pre booking, 3->offline booking by admin');
            $table->boolean('payment_type')->default(1)->comment('1->manual,2 ->upi payment');
            $table->float('waiting_charge')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rides');
    }
};
