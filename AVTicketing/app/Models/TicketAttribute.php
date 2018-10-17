<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketAttribute extends \Eloquent
{
	protected $table = 'TicketAttributes';
    protected $fillable = ['AttributeName', 'AttributeType', 'FreshdeskName'];
}
