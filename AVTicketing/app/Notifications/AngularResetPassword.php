<?php
/**
 * Created by PhpStorm.
 * User: joey
 * Date: 5/8/2017
 * Time: 11:46 AM
 */

namespace App\Notifications;


use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class AngularResetPassword extends ResetPassword
{

	/**
	 * Build the mail representation of the notification.
	 *
	 * @param  mixed  $notifiable
	 * @return \Illuminate\Notifications\Messages\MailMessage
	 */
	public function toMail($notifiable)
	{
		return (new MailMessage)
			->subject("AV Ticketing Password Reset")
			->line('You are receiving this email because we received a password reset request for your account.')
			->action('Reset Password', url('/#/password/reset', $this->token))
			->line('If you did not request a password reset, no further action is required.');
	}

}