<?php

namespace App\Channels;

use App\Notifications\TicketNotification;
use App\User;
use Aws\Sns\SnsClient;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Notifications\Notification;

class SMSChannel
{
	/**
	 * The HTTP client instance.
	 *
	 * @var \GuzzleHttp\Client
	 */
	protected $http;

	/**
	 * Create a new Slack channel instance.
	 *
	 * @param  \GuzzleHttp\Client  $http
	 * @return void
	 */
	public function __construct(HttpClient $http)
	{
		$this->http = $http;
	}

	/**
	 * Send the given notification.
	 *
	 * @param  User  $notifiable
	 * @param  \Illuminate\Notifications\Notification  $notification
	 * @return array
	 */
	public function send($notifiable, TicketNotification $notification)
	{
		$message = $notification->toSMS($notifiable);
		/** @var SnsClient $sns */
		$sns = \App::make('aws')->createClient('sns');
		$result = $sns->publish([
			'Message' => $message,
			'PhoneNumber' => '+1'.$notifiable->getSetting('phone_number')
		]);
		return $result->toArray();
	}
}
