<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserClassTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_class', function (Blueprint $table) {
            $table->increments('id');
	        $table->integer('user_id')->unsigned();
	        $table->integer('classInstance_id')->unsigned();
	        $table->foreign('user_id')->references('id')->on('users')
		        ->onUpdate('cascade')->onDelete('cascade');
	        $table->foreign('classInstance_id')->references('id')->on('classInstances')
		        ->onUpdate('cascade')->onDelete('cascade');
	        $table->date('date_added');
	        $table->date('date_expired')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_class');
    }
}
