<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TicketAndAttributes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::create('TicketStatus', function (Blueprint $table) {
		    $table->increments('id');
		    $table->string('Status');
		    $table->integer('FreshdeskID')->unsigned()->nullable();
		    $table->timestamps();
	    });
	    Schema::create('TicketPriority', function (Blueprint $table) {
		    $table->increments('id');
		    $table->string('Priority');
		    $table->integer('FreshdeskID')->unsigned()->nullable();
		    $table->timestamps();
	    });
	    Schema::create('TicketTypes', function (Blueprint $table) {
		    $table->increments('id');
		    $table->string('Type');
		    $table->timestamps();
	    });
	    Schema::create('TicketRequester', function (Blueprint $table) {
		    $table->increments('id');
		    $table->string('FirstName');
		    $table->string('LastName');
		    $table->string('Email');
		    $table->timestamps();
	    });

	    Schema::create('Tickets', function (Blueprint $table) {
		    $table->increments('id');
		    $table->integer('FreshdeskID')->unsigned();
		    $table->integer('StatusID')->unsigned();
		    $table->integer('PriorityID')->unsigned();
		    $table->integer('TypeID')->unsigned();
		    $table->integer('RequesterID')->unsigned();
		    $table->text('Subject');
		    $table->text('Description');
		    $table->timestamps();
		    $table->foreign('StatusID')->references('id')->on('TicketStatus')
			    ->onUpdate('cascade')->onDelete('cascade');
		    $table->foreign('PriorityID')->references('id')->on('TicketPriority')
			    ->onUpdate('cascade')->onDelete('cascade');
		    $table->foreign('TypeID')->references('id')->on('TicketTypes')
			    ->onUpdate('cascade')->onDelete('cascade');
		    $table->foreign('RequesterID')->references('id')->on('TicketRequester')
			    ->onUpdate('cascade')->onDelete('cascade');
	    });

	    Schema::create('TicketAttributes', function (Blueprint $table) {
		    $table->increments('id');
		    $table->string('AttributeName');
		    $table->string('AttributeType');
		    $table->string('FreshdeskName')->nullable();
		    $table->timestamps();
	    });

	    Schema::create('Ticket_StringAttributes', function (Blueprint $table) {
		    $table->increments('id');
		    $table->integer('TicketID')->unsigned();
		    $table->integer('AttributeID')->unsigned();
		    $table->text('AttributeValue');
		    $table->foreign('TicketID')->references('id')->on('Tickets')
			    ->onUpdate('cascade')->onDelete('cascade');
		    $table->foreign('AttributeID')->references('id')->on('TicketAttributes')
			    ->onUpdate('cascade')->onDelete('cascade');
		    $table->timestamps();
	    });
	    Schema::create('Ticket_BooleanAttributes', function (Blueprint $table) {
		    $table->increments('id');
		    $table->integer('TicketID')->unsigned();
		    $table->integer('AttributeID')->unsigned();
		    $table->boolean('AttributeValue');
		    $table->foreign('TicketID')->references('id')->on('Tickets')
			    ->onUpdate('cascade')->onDelete('cascade');
		    $table->foreign('AttributeID')->references('id')->on('TicketAttributes')
			    ->onUpdate('cascade')->onDelete('cascade');
		    $table->timestamps();
	    });
	    Schema::create('Ticket_IntegerAttributes', function (Blueprint $table) {
		    $table->increments('id');
		    $table->integer('TicketID')->unsigned();
		    $table->integer('AttributeID')->unsigned();
		    $table->integer('AttributeValue');
		    $table->foreign('TicketID')->references('id')->on('Tickets')
			    ->onUpdate('cascade')->onDelete('cascade');
		    $table->foreign('AttributeID')->references('id')->on('TicketAttributes')
			    ->onUpdate('cascade')->onDelete('cascade');
		    $table->timestamps();
	    });

	    Schema::create('Ticket_Time', function (Blueprint $table) {
		    $table->increments('id');
		    $table->integer('TicketID')->unsigned();
		    $table->decimal('EstimatedTime', 18, 2);
		    $table->foreign('TicketID')->references('id')->on('Tickets')
			    ->onUpdate('cascade')->onDelete('cascade');
		    $table->timestamps();
	    });

	    Schema::create('Ticket_EventData', function (Blueprint $table) {
		    $table->increments('id');
		    $table->integer('TicketID')->unsigned();
		    $table->string('EventName');
		    $table->date('EventDate');
		    $table->string('StartTime');
		    $table->foreign('TicketID')->references('id')->on('Tickets')
			    ->onUpdate('cascade')->onDelete('cascade');
		    $table->timestamps();
	    });

	    Schema::create('TicketUserStatus', function (Blueprint $table){
		    $table->increments('id');
		    $table->string('Status');
		    $table->timestamps();
	    });

	    Schema::create('Ticket_User', function (Blueprint $table){
		   $table->increments('id');
		    $table->integer('TicketID')->unsigned();
		    $table->integer('UserID')->unsigned();
		    $table->integer('StatusID')->unsigned();
		    $table->timestamps();
		    $table->foreign('TicketID')->references('id')->on('Tickets')
			    ->onUpdate('cascade')->onDelete('cascade');
		    $table->foreign('UserID')->references('id')->on('Users')
			    ->onUpdate('cascade')->onDelete('cascade');
		    $table->foreign('StatusID')->references('id')->on('TicketUserStatus')
			    ->onUpdate('cascade')->onDelete('cascade');
	    });

	    Schema::create('Ticket_IT', function (Blueprint $table){
		   $table->increments('id');
		    $table->integer('TicketID')->unsigned();
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
        Schema::dropIfExists('TicketStatus');
        Schema::dropIfExists('TicketPriority');
        Schema::dropIfExists('TicketTypes');
        Schema::dropIfExists('TicketRequester');
        Schema::dropIfExists('TicketAttributes');
        Schema::dropIfExists('Ticket_StringAttributes');
        Schema::dropIfExists('Ticket_BooleanAttributes');
        Schema::dropIfExists('Ticket_IntegerAttributes');
        Schema::dropIfExists('Ticket_Time');
        Schema::dropIfExists('Ticket_EventData');
        Schema::dropIfExists('Ticket_User');
        Schema::dropIfExists('Ticket_IT');
        Schema::dropIfExists('Tickets');
    }
}
