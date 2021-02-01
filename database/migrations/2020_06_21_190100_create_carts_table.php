<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart', function (Blueprint $table) {
            $table->increments('CartID');
            $table->string('TypeName',100);
            $table->integer('Quantity');
            $table->integer('Amount');
            $table->string('FoodCourt',100);
            $table->string('StoreName',100);
            $table->timestamp('DateTime');
            $table->integer('MealID')->unsigned()->index();
            $table->foreign('MealID')->references('MealID')->on('meal')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart');
    }
}
