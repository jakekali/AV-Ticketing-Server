<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TicketMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::create('Ticket_Message', function (Blueprint $table){
		    $table->increments('id');
		    $table->integer('TicketID')->unsigned();
		    $table->bigInteger('FreshdeskID')->unsigned();
		    $table->text("Message");
		    $table->string("FromEmail");
		    $table->timestamps();
		    $table->foreign('TicketID')->references('id')->on('Tickets')
			    ->onUpdate('cascade')->onDelete('cascade');
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::dropIfExists('Ticket_Message');
    }
}
