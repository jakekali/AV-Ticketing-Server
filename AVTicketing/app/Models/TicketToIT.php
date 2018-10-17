<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketToIT extends \Eloquent
{
	protected $table = 'Ticket_IT';
    protected $fillable = ['TicketID'];

	public function ticket(){
		return $this->belongsTo('App\Ticket', 'id', 'TicketID');
	}
}
