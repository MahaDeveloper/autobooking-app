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
        Schema::create('driver_proofs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('set null');
            $table->string('image')->nullable();
            $table->string('number');
            $table->boolean('type')->comment('1->vachicle image/number, 2->RC book image/number, 3->insurance image/number, 4->licence image/number, 5->aadhar card image/number');
            $table->json('details')->nullable();
            $table->boolean('verified')->default(0)->comment('1->verified, 0->not verified');
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
        Schema::dropIfExists('driver_proofs');
    }
};
