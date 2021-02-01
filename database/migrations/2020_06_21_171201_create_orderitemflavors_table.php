<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderitemflavorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orderitemflavor', function (Blueprint $table) {
            $table->increments('OrderItemFlavorID');
            $table->integer('OrderItemID')->unsigned()->index();;
            $table->foreign('OrderItemID')->references('OrderItemID')->on('orderitem')->onDelete('cascade');
            $table->integer('FlavorID')->unsigned()->index();;
            $table->foreign('FlavorID')->references('FlavorID')->on('flavor')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orderitemflavor');
    }
}
