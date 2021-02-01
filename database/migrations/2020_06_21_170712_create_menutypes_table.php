<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenutypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menutype', function (Blueprint $table) {
            $table->increments('MenuTypeID');
            $table->string('MenuTypeName',100);
            $table->integer('StoreID')->unsigned()->index();
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
        Schema::dropIfExists('menutype');
    }
}
