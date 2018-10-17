<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketStringAttribute extends \Eloquent
{
	protected $table = 'Ticket_StringAttributes';
    protected $fillable = ['AttributeValue'];
	
	public function attributeName(){
		return $this->belongsTo('App\TicketAttribute', 'AttributeID');
	}
}
