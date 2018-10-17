<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketMessage extends Model
{

	protected $fillable = ['TicketID', 'Message', 'FreshdeskID', 'FromEmail'];
    protected $table = 'Ticket_Message';

	public function ticket(){
		return $this->belongsTo('App\Ticket', 'TicketID');
	}
}
