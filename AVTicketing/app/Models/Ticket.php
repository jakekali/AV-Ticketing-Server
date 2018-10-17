<?php

namespace App;

use App\Notifications\TicketNotification;

class Ticket extends \Eloquent
{
	protected $table = 'Tickets';

	protected $fillable = ['id','FreshdeskID', 'Subject', 'Description'];

    public function ticketStringAttributes(){
	    return $this->belongsToMany('App\TicketAttribute', 'Ticket_StringAttributes', 'TicketID', 'AttributeID')
		    ->withPivot('AttributeValue')
		    ->withTimestamps();
    }

    public function ticketBooleanAttributes(){
	    return $this->belongsToMany('App\TicketAttribute', 'Ticket_BooleanAttributes', 'TicketID', 'AttributeID')
		    ->withPivot('AttributeValue')
		    ->withTimestamps();
    }

	public function ticketIntegerAttributes(){
	    return $this->belongsToMany('App\TicketAttribute', 'Ticket_IntegerAttributes', 'TicketID', 'AttributeID')
		    ->withPivot('AttributeValue')
		    ->withTimestamps();
    }

	public function priority(){
		return $this->belongsTo('App\TicketPriority', 'PriorityID');
	}

	public function type(){
		return $this->belongsTo('App\TicketType', 'TypeID');
	}

	public function status(){
		return $this->belongsTo('App\TicketStatus', 'StatusID');
	}

	public function requester(){
		return $this->belongsTo('App\TicketRequester', 'RequesterID');
	}

	public function eventData(){
		return $this->hasOne('App\TicketEventData', 'TicketID');
	}

	public function time(){
		return $this->hasOne('App\TicketTime', 'TicketID');
	}

	public function users(){
		return $this->hasMany('App\TicketUser', 'TicketID');
	}

	public function messages(){
		return $this->hasMany('App\TicketMessage', 'TicketID');
	}

	public function getFreshdeskFieldArray(){
		$returnArray = array();
		$attributeTypes = array("String", "Boolean", "Integer");
		foreach ($attributeTypes as $attributeType) {
			$attributeVariable = "ticket".$attributeType."Attributes";
			foreach ($this->$attributeVariable as $ticketAttribute) {
				$returnArray[$ticketAttribute->FreshdeskName] = $ticketAttribute->pivot->AttributeValue;
				if($attributeType == "Boolean"){
					$returnArray[$ticketAttribute->FreshdeskName] = (bool)$ticketAttribute->pivot->AttributeValue;
				}
			}
		}
		if($this->Type == "Event"){
			$eventVars = array("EventName", "EventDate", "StartTime");
			foreach ($eventVars as $eventVar) {
				$freshdeskName = TicketAttribute::where("AttributeName", "=", $eventVar)->get()[0]->FreshdeskName;
				$returnArray[$freshdeskName] = $this->eventData->$eventVar;
			}
		}
		if($this->time != null && $this->time->EstimatedTime != null){
			$freshdeskName = TicketAttribute::where("AttributeName", "=", "EstimatedTime")->get()[0]->FreshdeskName;
			$returnArray[$freshdeskName] = $this->time->EstimatedTime;
		}
		/** Event building is white house because freshdesk is dumb and won't accept HS */
		$returnArray['event_building'] = "White House";
		$returnArray['microphone'] = (string)$returnArray['microphone']."";
		return $returnArray;
	}

	public function addUser($userID){
		if(TicketUser::where('UserID', '=', $userID)->where('TicketID', '=', $this->id)->count() == 0) {
			$this->users()->create([
				'UserID' => $userID,
				'StatusID' => TicketUserStatus::where('Status', '=', 'Assigned')->get()->first()->id
			]);
			$this->notifyUsers("assigned");
		}
	}

	/**
	 * @param int $id
	 **/
	public static function getTicketData($id){
		$result = Ticket::where('id', '=', $id)
			->with('type', 'requester', 'users.user', 'users.user.settings', 'eventData',
				'ticketStringAttributes', 'ticketBooleanAttributes', 'ticketIntegerAttributes',
				'users.status', 'status', 'messages', 'toIT')
			->get()->first();
		if(!is_null($result)) {
            $result->created_at_formatted = (new \DateTime($result->created_at))->format(DATE_ISO8601);
            foreach ($result->users as $key => $user) {
            	$userData = $user->user;
            	$userStatus = $user->status->Status;
                $result->users[$key] = $user->user;
                $result->users[$key]['status'] = $userStatus;
            }
            return $result;
        }
        else{
		    return null;
        }
	}

	public function getSlackFormattedTicketArray(){
		//This function is used to create the DATA ARRAY for ticket information.
		$dataString = array(
			"attachments" => array(
				array(
					"fallback" => "Ticket #".$this->id." was requested by " . $this->requester->First_Name . " "
						. $this->requester->Last_Name .
						" regarding ".$this->Subject.". Description: ".$this::cutString(strip_tags($this->Description),200).".",
					"color" => "#36a64f",
					"pretext"=> "Here is the current information about ticket #".$this->id
						." to the best of Jacob's programming and spelling abilities.",
					"author_name"=> $this->requester->FirstName . " " . $this->requester->LastName,
					"title" => $this->Subject,
					"title_link" => "http://ec2-54-183-225-230.us-west-1.compute.amazonaws.com/#/ticket/".(int)$this->id,
					"text"=> $this::cutString(strip_tags($this->Description),200),
					"fields" => array(
						array(

							"title"=> "Priority",
							"value"=> $this->priority->Priority,
							"short"=> true
						), array(

							"title"=> "Status",
							"value"=> $this->status->Status,
							"short"=> true
						), array(

							"title"=> "Event Date",
							"value"=> $this->getEventDate(),
							"short"=> (strlen($this->getEventDate())<13)
						),
						array(

							"title"=> "Location",
							"value"=> $this->getEventLocation(),
							"short"=> (strlen($this->getEventLocation())<13)
						)

					),
					"image_url" => "http://my-website.com/path/to/image.jpg",
					"thumb_url" => "http://example.com/path/to/thumb.png",
					"footer" => "AV Ticketing API",
					"footer_icon" => "https://yt3.ggpht.com/-SAvIaftjJVs/AAAAAAAAAAI/AAAAAAAAAAA/psBGJ9S5pQA/s100-c-k-no-mo-rj-c0xffffff/photo.jpg",
					"ts"=> time(),
				)
			)
		);
		return $dataString;
	}


	/**
	 * used to cut strings, at the end of word. for max numbers of character
	 * see http://stackoverflow.com/questions/9421164/display-string-to-a-maximum-of-so-many-characters-without-splitting-word
	 * @param string $string
	 * @param int $maxLength
	 * @return string
	 */
	public static function cutString($string, $maxLength){
		if (strlen($string) > $maxLength) {
			$stringCut = substr($string, 0, $maxLength);
			$string = substr($stringCut, 0, strrpos($stringCut, ' '));
		}

		return $string;
	}


	public function getEventDate(){
		$date="None";
		if($this->EventData!=null) {
			/** @var \DateTime $date */
			$date = $this->EventData->EventDate;
			if ($date == null) {
				$date = "NO DATE";
			} else {
				$date = $date->format("m/d/Y");
			}
			$eventTime = $this->EventData->StartTime;
			$date = $date . " " . $eventTime;
		}else{
			$date = "NON";
		}
		return $date;
	}

	public function getEventLocation(){
		$location = "The Moon";
		foreach($this->ticketStringAttributes as $attribute){
			if($attribute->AttributeName == "eventLocation"){
				$location = $attribute->pivot->AttributeValue;
			}
		}
		return $location;
	}

	public static function fromFreshdesk(array $args)
	{
		/** @var Ticket $ticket */
		$ticket = Ticket::firstOrNew(['FreshdeskID' => $args['freshdeskID']]);
		$ticket->Subject = $args['subject'];
		$ticket->Description = $args['description'];

		if (array_key_exists("status", $args)) {
			$statusObject = TicketStatus::firstOrCreate(['Status' => $args['status']]);
			$ticket->status()->associate($statusObject);
		}
		if (array_key_exists("priority", $args)){
			$priorityObject = TicketPriority::firstOrCreate(['Priority' => $args['priority']]);
			$ticket->priority()->associate($priorityObject);
		}
		if (array_key_exists("type", $args)) {
			$ticketTypeObject = TicketType::firstOrCreate(['Type' => $args['type']]);
			$ticket->type()->associate($ticketTypeObject);
		}
		if (array_key_exists("requester", $args)) {
			$ticketRequester = TicketRequester::firstOrCreate([
				'FirstName' => $args['requester']['requestedByFirstName'],
				'LastName' => $args['requester']['requestedByLastName'],
				'Email' => $args['requester']['requestedByEmail']
			]);
			$ticket->requester()->associate($ticketRequester);
		}
		$ticket->save();

		$eventData = $args['eventData'];
		if(!empty($eventData['startTime'])){
			$eventAttrsArray = [
				'TicketID' => $ticket->id
			];

			if(!count($ticket->eventData)){
				$eventObject = TicketEventData::firstOrNew($eventAttrsArray);
				$eventObject->EventName = $eventData['eventName'];
				$eventObject->EventDate = date_create_from_format("M j, Y", $eventData['eventDate']);
				$eventObject->StartTime = $eventData['startTime'];
				$eventObject->save();
				$ticket->eventData()->save($eventObject);
			}
			else{
				$eventAttrsArray['EventName'] = $eventData['eventName'];
				$eventAttrsArray['EventDate'] = date_create_from_format("M j, Y", $eventData['eventDate']);
				$eventAttrsArray['StartTime'] = $eventData['startTime'];
				$ticket->eventData()->update($eventAttrsArray);
			}
		}
		else{
			\DB::table("Ticket_EventData")->where("TicketID", '=', $ticket->id)->delete();
		}
		if(!empty($eventData['estimatedTime'])){
			$ticketTimeArray = [
				'TicketID' => $ticket->id,
				'EstimatedTime' => $eventData['estimatedTime']
			];
			if(!count($ticket->time)){
				$ticketTime = TicketTime::firstOrNew($ticketTimeArray);
				$ticket->time()->save($ticketTime);
			}
			else{
				$ticket->time()->update($ticketTimeArray);
			}
		}
		else{
			\DB::table("Ticket_Time")->where("TicketID", "=", $ticket->id)->delete();
		}


		$attributeTypes = ['bool'=>'Boolean', 'integer'=>'Integer', 'string'=>'String'];
		foreach($attributeTypes as $jsonType => $dbType) {
			$attributes = $args[$jsonType.'Attributes'];
			foreach ($attributes as $attribute => $value) {
				$ticketAttributeFunctionName = "ticket" . $dbType . "Attributes";
				if (!empty($value)) {
					$value = $dbType == "Boolean" ? (int)filter_var($value, FILTER_VALIDATE_BOOLEAN): $value;
					$ticketAttribute = TicketAttribute::firstOrCreate([
						'AttributeName' => $attribute,
						'AttributeType' => $dbType
					]);
					if (!$ticket->$ticketAttributeFunctionName()->where([
						'AttributeID' => $ticketAttribute->id
					])->get()->count()
					) {
						$ticket->$ticketAttributeFunctionName()->attach($ticketAttribute->id, ['AttributeValue' => $value]);
					}
				}
				else{
					$ticketAttribute = TicketAttribute::where("AttributeName", '=', $attribute)->get();
					echo "Detaching " . $attribute . " ".$ticketAttributeFunctionName;
					if(count($ticketAttribute) > 0)
						$ticket->$ticketAttributeFunctionName()->detach($ticketAttribute[0]->id);
				}
			}
		}
		return $ticket;
	}
	public function notifyUsers($notificationType){
		$notificationArray = array(
			"new" => "Ticket ID " . $this->id . " has been created.",
			"update" => "Ticket ID " . $this->id . " has been updated.",
			"assigned" => "Ticket ID " . $this->id . " has been assigned to you."
		);
		if(count($this->users) > 0) {
			foreach ($this->users as $user) {
				/** @var User $user->user */
				$user->user->notify(new TicketNotification($this, $notificationType));
			}
		}
		else{
			$admins = User::getAdmins();
			foreach ($admins as $admin){
				/** @var User $admin */
				$admin->notify(new TicketNotification($this, $notificationType));
			}
		}
	}

	public function removeUser($userID){
		TicketUser::where('TicketID', '=', $this->id)
			->where('UserID', '=', $userID)
			->delete();
	}

	public function toIT(){
		return $this->hasOne('App\TicketToIT', 'TicketID', 'id');
	}
}
