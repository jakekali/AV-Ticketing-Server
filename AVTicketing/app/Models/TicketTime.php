<?php

namespace App;

class TicketTime extends \Eloquent
{
    protected $table = 'Ticket_Time';
	protected $fillable = ['EstimatedTime'];

	public function ticket(){
		return $this->belongsTo('App\Ticket', 'TicketID');
	}
}
