<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoreBusinessHourTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('storebusinesshour', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('StoreID')->unsigned()->index();
            $table->foreign('StoreID')->references('StoreID')->on('store')->onDelete('cascade');
            $table->time('BusinessHour');
            $table->boolean('StoreState');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('storebusinesshour');
    }
}
