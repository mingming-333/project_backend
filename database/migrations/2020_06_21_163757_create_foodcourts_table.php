<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFoodcourtsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('foodcourt', function (Blueprint $table) {
            $table->increments('FoodCourtID');
            $table->string('FoodCourtName',100);
            $table->string('FoodCourtDescription',300);
            $table->integer('SuperUserID')->unsigned()->index();
            $table->foreign('SuperUserID')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('foodcourt');
    }
}
