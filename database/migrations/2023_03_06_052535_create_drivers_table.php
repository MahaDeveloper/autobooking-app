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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('mobile')->unique();
            $table->string('image')->nullable();
            $table->string('fcm_id')->nullable();
            $table->boolean('verification_status')->default(0)->comment('0->requested, 2->proof updated, 2->accepted, 3->rejected');
            $table->boolean('current_status')->default(0)->comment('0->active,1->online, 2->offline, 3->in ride, 4->payment pending, 5->subscription ended, 6->disabled');
            $table->float('latitude')->nullable();
            $table->float('longitude')->nullable();
            $table->dateTime('checkin_time')->nullable();
            $table->unsignedBigInteger('refferal_id')->nullable();
            $table->foreign('refferal_id')->references('id')->on('drivers')->onDelete('set null');
            $table->date('subscription_end_date')->nullable();
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
        Schema::dropIfExists('drivers');
    }
};
