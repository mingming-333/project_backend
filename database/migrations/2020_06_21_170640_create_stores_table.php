<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store', function (Blueprint $table) {
            $table->increments('StoreID');
            $table->string('StoreName',100);
            $table->integer('StoreTheme');
            $table->string('StoreDescription',300);
            $table->integer('FoodCourtID')->unsigned()->index();
            $table->foreign('FoodCourtID')->references('FoodCourtID')->on('foodcourt')->onDelete('cascade');
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
        Schema::dropIfExists('store');
    }
}
