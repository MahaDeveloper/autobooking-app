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
        Schema::table('ride_billing_details', function (Blueprint $table) {

            $table->boolean('ride_type')->default(1)->comment('0->ride now, 1-.change ride');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ride_billing_details', function (Blueprint $table) {
            //
        });
    }
};
