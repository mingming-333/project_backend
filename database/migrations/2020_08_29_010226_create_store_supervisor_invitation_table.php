<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoreSupervisorInvitationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_supervisor_invitation', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('StoreID')->unsigned()->index();
            $table->foreign('StoreID')->references('StoreID')->on('store')->onDelete('cascade');
            $table->string('Email');
            $table->string('Token');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_supervisor_invitation');
    }
}
