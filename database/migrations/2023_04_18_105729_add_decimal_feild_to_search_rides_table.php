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

            $table->decimal('pickup_latitude',23,20)->change();
            $table->decimal('pickup_longitude',23,20)->change();
            $table->decimal('drop_latitude',23,20)->change();
            $table->decimal('drop_longitude',23,20)->change();
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
