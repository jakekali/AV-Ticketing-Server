<?php

namespace App;

class TicketPriority extends \Eloquent
{
	protected $table = 'TicketPriority';
    protected $fillable = ['Priority', 'FreshdeskID'];
}
