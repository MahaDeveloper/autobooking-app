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
        Schema::create('peak_charges', function (Blueprint $table) {
            $table->id();
            $table->time('from_time');
            $table->time('to_time');
            $table->float('percentage');
            $table->boolean('type')->default(1)->comment('1->peak, 2->night peak charge');
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
        Schema::dropIfExists('peak_charges');
    }
};
