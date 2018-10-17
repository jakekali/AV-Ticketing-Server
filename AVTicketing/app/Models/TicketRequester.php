<?php

namespace App;

class TicketRequester extends \Eloquent
{
	protected $table = 'TicketRequester';
    protected $fillable = ['FirstName', 'LastName', 'Email'];

    public function __toString()
    {
        return $this->FirstName . " " . $this->LastName;
    }
}
