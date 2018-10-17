<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketUser extends Model
{

	protected $fillable = ['TicketID', 'UserID', 'StatusID'];
    protected $table = 'Ticket_User';

	public function ticket(){
		return $this->belongsTo('App\Ticket', 'TicketID');
	}

	public function user(){
		return $this->belongsTo('App\User', 'UserID');
	}

	public function status(){
		return $this->belongsTo('App\TicketUserStatus', 'StatusID');
	}
}
