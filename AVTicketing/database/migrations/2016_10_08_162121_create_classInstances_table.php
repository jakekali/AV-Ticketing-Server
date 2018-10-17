<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClassInstancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('classInstances', function (Blueprint $table) {
            $table->increments('id');
	        $table->integer('schoolClassID')->unsigned();
	        $table->integer('roomID')->unsigned();
	        $table->integer('periodID')->unsigned();
	        $table->foreign('schoolClassID')->references('id')->on('schoolClasses')
		        ->onUpdate('cascade')->onDelete('cascade');
	        $table->foreign('roomID')->references('id')->on('rooms')
		        ->onUpdate('cascade')->onDelete('cascade');
	        $table->foreign('periodID')->references('id')->on('periods')
		        ->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('classInstances');
    }
}
