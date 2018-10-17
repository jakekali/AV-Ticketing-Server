<?php

namespace App\Channels;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackAttachmentField;

class SlackAPIChannel
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
	 * @param  mixed  $notifiable
	 * @param  \Illuminate\Notifications\Notification  $notification
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function send($notifiable, Notification $notification)
	{
		if (! $url = $notifiable->routeNotificationFor('slack')) {
			return;
		}

		$message = $notification->toSlack($notifiable);

		echo "URL = " . $url . "<br>";
		echo json_encode($this->buildJsonPayload($message));
		$result = $this->http->post($url, $this->buildJsonPayload($message));
		echo $result->getBody();

	}

	/**
	 * Build up a JSON payload for the Slack webhook.
	 *
	 * @param  \Illuminate\Notifications\Messages\SlackMessage  $message
	 * @return array
	 */
	protected function buildJsonPayload(SlackMessage $message)
	{
		$optionalFields = array_filter([
			'username' => data_get($message, 'username'),
			'icon_emoji' => data_get($message, 'icon'),
			'channel' => data_get($message, 'channel'),
			'token' => env("SLACK_TOKEN"),
			'user' => '@av.ticketing',
			'as_user' => true
		]);

		return array_merge([
			'form_params' => array_merge([
				'text' => $message->content,
				'attachments' => json_encode($message->attachments),
			], $optionalFields),
		], $message->http);
	}

	/**
	 * Format the message's attachments.
	 *
	 * @param  \Illuminate\Notifications\Messages\SlackMessage  $message
	 * @return array
	 */
	protected function attachments(SlackMessage $message)
	{
		return collect($message->attachments)->map(function ($attachment) use ($message) {
			$attachment = json_encode($attachment);
			return array_filter([
				'color' => $attachment->color
					?: $message->color(),
				'title' => $attachment->title,
				'text' => $attachment->content,
				'fallback' => $attachment->fallback,
				'title_link' => $attachment->url,
				'fields' => $this->fields($attachment),
				'mrkdwn_in' => $attachment->markdown,
				'footer' => $attachment->footer,
				'footer_icon' => $attachment->footerIcon,
				'ts' => $attachment->timestamp,
			]);
		})->all();
	}

	/**
	 * Format the attachment's fields.
	 *
	 * @param  \Illuminate\Notifications\Messages\SlackAttachment  $attachment
	 * @return array
	 */
	protected function fields(SlackAttachment $attachment)
	{
		return collect($attachment->fields)->map(function ($value, $key) {
			if ($value instanceof SlackAttachmentField) {
				return $value->toArray();
			}

			return ['title' => $key, 'value' => $value, 'short' => true];
		})->values()->all();
	}
}
