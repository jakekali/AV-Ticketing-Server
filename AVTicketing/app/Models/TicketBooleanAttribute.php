<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketBooleanAttribute extends \Eloquent
{
	protected $table = 'Ticket_BooleanAttributes';
	protected $fillable = ['AttributeValue'];

	public function attributeName(){
		return $this->belongsTo('App\TicketAttribute', 'AttributeID');
	}
}
