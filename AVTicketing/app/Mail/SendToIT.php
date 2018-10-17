<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendToIT extends Mailable
{
    use Queueable, SerializesModels;

    private $ticketID;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($ticketID)
    {
        $this->ticketID = $ticketID;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('av@flatbush.org', 'AV Ticketing')
	        ->to('mgoldfein@flatbush.org')
	        ->subject("Ticket " . $this->ticketID . " Sent To IT")
            ->view('toIT')
	        ->with([
	        	"TicketID" => $this->ticketID
	        ]);
    }
}
