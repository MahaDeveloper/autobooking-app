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
        Schema::table('search_rides', function (Blueprint $table) {

            $table->unsignedBigInteger('driver_log_id')->nullable();
            $table->foreign('driver_log_id')->references('id')->on('drivers')->onDelete('set null');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('search_rides', function (Blueprint $table) {
            //
        });
    }
};
