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
        Schema::create('search_rides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->text('pickup_address');
            $table->text('drop_address');
            $table->float('pickup_latitude');
            $table->float('pickup_longitude');
            $table->float('drop_latitude');
            $table->float('drop_longitude');
            $table->float('amount');
            $table->float('distance');
            $table->json('first_sent_drivers');
            $table->json('second_sent_drivers')->nullable();
            $table->json('rejected_drivers')->nullable();
            $table->dateTime('search_time')->nullable();
            $table->boolean('status')->default(0)->comment('0->searching, 1->driverr not allocated, 2->ride allocated, 3->cancelled, 4->prebooking');
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
        Schema::dropIfExists('search_rides');
    }
};
