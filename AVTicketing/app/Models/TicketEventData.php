<?php

namespace App;

class TicketEventData extends \Eloquent
{
    protected $table = 'Ticket_EventData';
	protected $fillable = ['EventName', 'StartTime', 'EventDate', 'TicketID'];
	protected $dates = [
		'created_at',
		'updated_at',
		'EventDate'
	];

	public function ticket(){
		return $this->belongsTo('App\Ticket', 'TicketID');
	}
}
