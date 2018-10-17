<?php

namespace App\Notifications;

use App\Channels\SlackAPIChannel;
use App\Channels\SMSChannel;
use App\Ticket;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TicketNotification extends Notification
{
    use Queueable;
    private $ticket;
    private $notificationType;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Ticket $ticket, $notificationType)
    {
        $this->ticket = $ticket;
        $this->notificationType = $notificationType;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
    	/** @var User $notifiable */
    	if(!empty($notifiable->getSetting('slack_id')))
		    return [SlackAPIChannel::class];
        else
        	return [SMSChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', 'https://laravel.com')
                    ->line('Thank you for using our application!');
    }

	/**
	 * @param $notifiable
	 * @return SlackMessage
	 */
	public function toSlack($notifiable){
		/** @var User $notifiable */
		$message = new SlackMessage();
		if($this->notificationType == "new"){
			$message->content("Ticket ID " . $this->ticket->id . " has been added and needs to be assigned.");
		}
		else if($this->notificationType == "update"){
			$message->content("Ticket ID " . $this->ticket->id . " has been updated.");
		}
		else if($this->notificationType == "assigned"){
			$message->content("Ticket ID " . $this->ticket->id . " has been assigned to you.");
		}
		else if($this->notificationType == "message"){
			$message->content("Ticket ID " . $this->ticket->id . " has received a new message.");
		}
		$message->channel = $notifiable->getSetting('slack_id');
		/** @var Ticket $ticket */
		$ticket = $this->ticket;
		$message->attachments = $ticket->getSlackFormattedTicketArray()['attachments'];
		return $message;
    }


	/**
	 * @param $notifiable
	 * @return string $message
	 */
	public function toSMS($notifiable){
		/** @var User $notifiable */
		$message = "";
		if($this->notificationType == "new"){
			$message = "Ticket ID " . url('/#/ticket', $this->ticket->id ). " has been added and needs to be assigned.";
		}
		else if($this->notificationType == "update"){
			$message = "Ticket ID " .  url('/#/ticket', $this->ticket->id ) . " has been updated.";
		}
		else if($this->notificationType == "assigned"){
			$message = "Ticket ID " .  url('/#/ticket', $this->ticket->id ) . " has been assigned to you.";
		}
		else if($this->notificationType == "message"){
			$message = "Ticket ID " .  url('/#/ticket', $this->ticket->id ) . " has received a new message.";
		}
		return $message;
    }



    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
