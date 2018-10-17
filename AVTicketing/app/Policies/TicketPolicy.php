<?php

namespace App\Policies;

use App\Ticket;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function view(User $user, Ticket $ticket){
	    $result = \DB::table('Tickets')
		    ->select([
			    'Tickets.id as TicketID'
		    ])
		    ->join('Ticket_User', 'Ticket_User.TicketID', '=', 'Tickets.id')
		    ->where('Ticket_User.UserID', '=', $user->id)
		    ->where('Tickets.id', '=', $ticket->id)
		    ->get();
	    return $user->hasPermission('readAllTickets') || count($result) == 1;
    }

    public function update(User $user, Ticket $ticket){
	    $result = \DB::table('Tickets')
		    ->select([
			    'Tickets.id as TicketID'
		    ])
		    ->join('Ticket_User', 'Ticket_User.TicketID', '=', 'Tickets.id')
		    ->where('Ticket_User.UserID', '=', $user->id)
		    ->where('Tickets.id', '=', $ticket->id)
		    ->get();
	    return $user->hasPermission('writeAllTickets') || count($result) == 1;
    }
}
