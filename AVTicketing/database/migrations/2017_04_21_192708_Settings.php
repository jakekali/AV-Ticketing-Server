<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Settings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::create('settings', function (Blueprint $table){
		    $table->increments('id');
		    $table->string('name');
	    });

	    Schema::create('user_settings', function (Blueprint $table){
	    	$table->increments('id');
	    	$table->integer('user_id')->unsigned();
	    	$table->integer('setting_id')->unsigned();
		    $table->foreign('user_id')->references('id')->on('users')
			    ->onUpdate('cascade')->onDelete('cascade');
		    $table->foreign('setting_id')->references('id')->on('settings')
			    ->onUpdate('cascade')->onDelete('cascade');
	    	$table->string('value');
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Todo: finish
    }
}
