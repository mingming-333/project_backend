<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryPage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_page', function (Blueprint $table) {
            $table->increments('OrderID');
            $table->integer('Price');
            $table->integer('Status');
            $table->String('Memo',300);
            $table->integer('StoreID')->unsigned()->index();
            $table->foreign('StoreID')->references('StoreID')->on('store')->onDelete('cascade');
            $table->integer('CustomerID')->unsigned()->index();
            $table->foreign('CustomerID')->references('id')->on('users')->onDelete('cascade');
            $table->integer('ServiceFee');
            $table->string('Destination');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_page');
    }
}
