<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRequireMultipleToFlavorTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('flavortype', function (Blueprint $table) {
            $table->boolean('isRequired')->default(false);
            $table->boolean('isMultiple')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('flavortype', function (Blueprint $table) {
            //
        });
    }
}
