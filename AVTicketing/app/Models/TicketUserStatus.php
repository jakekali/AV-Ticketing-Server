<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketUserStatus extends Model
{
	protected $fillable = ['Status', 'id'];
    protected $table = 'TicketUserStatus';
}
