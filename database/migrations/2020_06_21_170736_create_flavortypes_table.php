<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFlavortypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('flavortype', function (Blueprint $table) {
            $table->increments('FlavorTypeID');
            $table->string('FlavorTypeName',100);
            $table->integer('StoreID')->unsigned()->index();;
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
        Schema::dropIfExists('flavortype');
    }
}
