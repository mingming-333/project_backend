<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBusinessDayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('StoreBusinessDay', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('BusinessDay');
            $table->integer('StoreID')->unsigned()->index();
            $table->foreign('StoreID')->references('StoreID')->on('store')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_day');
    }
}
