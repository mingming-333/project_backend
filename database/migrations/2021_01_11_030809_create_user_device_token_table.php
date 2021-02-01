<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDeviceTokenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('UserDeviceToken', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('UserID')->unsigned()->index();
            $table->foreign('UserID')->references('id')->on('users')->onDelete('cascade');
            $table->string('DeviceToken', 300);
            $table->timestamp('LatestTime')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('UserDeviceToken');
    }
}
