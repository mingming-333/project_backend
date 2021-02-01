<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFlavorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flavor', function (Blueprint $table) {
            $table->increments('FlavorID');
            $table->string('FlavorName',100);
            $table->integer('ExtraPrice');
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
        Schema::dropIfExists('flavor');
    }
}
