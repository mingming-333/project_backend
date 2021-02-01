<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMealsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meal', function (Blueprint $table) {
            $table->increments('MealID');
            $table->string('MealName',100);
            $table->integer('MealPrice');
            $table->boolean('MealToday');
            $table->boolean('MealSoldOut');
            $table->string('MealImagePath',200);
            $table->integer('MealCalorie');
            $table->string('MealDescription',200);
            $table->integer('MenuTypeID')->unsigned()->index();
            $table->foreign('MenuTypeID')->references('MenuTypeID')->on('menutype')->onDelete('cascade');
        
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meal');
    }
}
