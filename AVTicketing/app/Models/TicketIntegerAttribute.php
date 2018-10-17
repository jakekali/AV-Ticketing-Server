<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketIntegerAttribute extends \Eloquent
{
	protected $table = 'Ticket_IntegerAttributes';
	protected $fillable = ['AttributeValue'];

	public function attributeName(){
		return $this->belongsTo('App\TicketAttribute', 'AttributeID');
	}
}
