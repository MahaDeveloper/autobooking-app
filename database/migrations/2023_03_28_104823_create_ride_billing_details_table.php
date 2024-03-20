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
        Schema::create('ride_billing_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ride_id')->nullable();
            $table->foreign('ride_id')->references('id')->on('rides')->onDelete('set null');
            $table->text('pickup_address');
            $table->text('drop_address');
            $table->float('pickup_latitude')->nullable();
            $table->float('pickup_longitude')->nullable();
            $table->float('drop_latitude')->nullable();
            $table->float('drop_longitude')->nullable();
            $table->float('amount')->nullable();
            $table->float('distance')->nullable();
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
        Schema::dropIfExists('ride_billing_details');
    }
};
