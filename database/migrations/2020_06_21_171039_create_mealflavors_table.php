<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMealflavorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mealflavor', function (Blueprint $table) {
            $table->increments('MealFlavorID');
            $table->integer('MealID')->unsigned()->index();;
            $table->foreign('MealID')->references('MealID')->on('meal')->onDelete('cascade');
            $table->integer('FlavorTypeID')->unsigned()->index();;
            $table->foreign('FlavorTypeID')->references('FlavorTypeID')->on('flavortype')->onDelete('cascade');
        
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mealflavor');
    }
}
