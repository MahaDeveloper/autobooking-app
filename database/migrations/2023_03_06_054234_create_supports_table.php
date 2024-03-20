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
        Schema::dropIfExists('supports');
        Schema::create('supports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supportable_id');
            $table->string('supportable_type');
            $table->text('description');
            $table->text('reply_msg');
            $table->boolean('status')->default(0)->comment('0->raised, 1->replied');
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
        Schema::dropIfExists('supports');
    }
};
