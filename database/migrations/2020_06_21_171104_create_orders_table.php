<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('OrderID');
            $table->integer('Price');
            $table->integer('Status');
            $table->String('Memo',300);
            $table->integer('StoreID')->unsigned()->index();
            $table->foreign('StoreID')->references('StoreID')->on('store')->onDelete('cascade');
            $table->integer('CustomerID')->unsigned()->index();
            $table->foreign('CustomerID')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
