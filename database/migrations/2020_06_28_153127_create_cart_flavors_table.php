<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCartFlavorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cartflavor', function (Blueprint $table) {
            $table->increments('CartFlavorID');
            $table->integer('CartID')->unsigned()->index();
            $table->foreign('CartID')->references('CartID')->on('cart')->onDelete('cascade');
            $table->integer('FlavorTypeID')->unsigned()->index();
            $table->foreign('FlavorTypeID')->references('FlavorTypeID')->on('flavortype')->onDelete('cascade');
            $table->integer('FlavorID')->unsigned()->index();
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
        Schema::dropIfExists('cartflavor');
    }
}
