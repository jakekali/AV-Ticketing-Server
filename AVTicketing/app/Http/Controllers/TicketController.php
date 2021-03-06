<?php

namespace App\Http\Controllers;

use App\Mail\SendToIT;
use App\Notifications\TicketNotification;
use App\Ticket;
use App\TicketAttribute;
use App\TicketEventData;
use App\TicketMessage;
use App\TicketPriority;
use App\TicketRequester;
use App\TicketStatus;
use App\TicketTime;
use App\TicketToIT;
use App\TicketType;
use App\TicketUser;
use App\TicketUserStatus;
use App\User;
use Carbon\Carbon;
use Freshdesk\Exceptions\ApiException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;


class TicketController extends Controller
{
    public function newWebHookTicket(Request $request){
	    $ticket = Ticket::fromFreshdesk($request->all());
	    $this->notifyUsers($ticket, "new");
    }

    public function notifyUsers(Ticket $ticket, $notificationType){
    	$ticket->notifyUsers($notificationType);
    }

    public function updateWebHookTicket(Request $request, $freshdeskID){
	    $ticket = Ticket::fromFreshdesk($request->all());
	    $this->notifyUsers($ticket, "update");
    }

    public function updateFreshdesk(Request $request, Ticket $ticket){
    	$freshdeskData = array(
    		"status" => $ticket->status->FreshdeskID,
		    "priority" => $ticket->priority->FreshdeskID,
		    "source" => 2,
		    "custom_fields" => $ticket->getFreshdeskFieldArray()
	    );
    	try{
    		\Freshdesk::tickets()->update($ticket->FreshdeskID, $freshdeskData);
	    }
	    catch (ApiException $exception){
    		echo $exception->getRequestException()->getRequest()->getBody() . "<br><br>";
		    echo $exception->getRequestException()->getResponse()->getBody();
	    }
    }

	public function getUnassignedTickets(Request $request){
		//Need to fill in code
		$result = \DB::table('Tickets')
					->select([
						'Tickets.id as TicketID',
						'TicketStatus.Status as TicketStatus',
						'TicketPriority.Priority as TicketPriority',
						'TicketTypes.Type as TicketType',
						'Tickets.Subject as TicketSubject'

					])
					->join('TicketStatus', 'Tickets.StatusID', '=', 'TicketStatus.id')
					->join('TicketPriority', 'Tickets.PriorityID', '=', 'TicketPriority.id')
					->join('TicketTypes', 'Tickets.TypeID', '=', 'TicketTypes.id')
					->leftJoin("Ticket_User", 'Tickets.id', '=', 'Ticket_User.TicketID')
					->whereNull('Ticket_User.id')
					->get();
		return $result;
	}

	public function getSoonTickets(Request $request){
		//Need to fill in code
		$result = \DB::select(\DB::raw("select `Tickets`.`id` as `TicketID`, `TicketStatus`.`Status` as `TicketStatus`, `TicketPriority`.`Priority` as `TicketPriority`, `TicketTypes`.`Type` as `TicketType`, `Tickets`.`Subject` as `TicketSubject` 
from `Tickets` 
inner join `TicketStatus` on `Tickets`.`StatusID` = `TicketStatus`.`id` 
inner join `TicketPriority` on `Tickets`.`PriorityID` = `TicketPriority`.`id` 
inner join `TicketTypes` on `Tickets`.`TypeID` = `TicketTypes`.`id` 
left join `Ticket_User` on `Tickets`.`id` = `Ticket_User`.`TicketID` 
left join `Ticket_EventData` on `Tickets`.`id` = `Ticket_EventData`.`TicketID` 
where `Tickets`.`created_at` 
BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
or `Ticket_EventData`.`EventDate` BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)"));
		return $result;
	}

	public function getNewMessages(Request $request, $freshdeskID){
		$ticket = Ticket::where("FreshdeskID", '=', $freshdeskID)->get()[0];
		$messages = \Freshdesk::tickets()->conversations($freshdeskID);
		foreach ($messages as $message) {
			/** @var TicketMessage $ticketMessage */
			$ticketMessage = TicketMessage::firstOrNew(['FreshdeskID'=>$message['id']]);
			$ticketMessage->Message = $message['body_text'];
			$ticketMessage->FromEmail = empty($message['from_email']) ? "Note" : $message['from_email'];
			$ticketMessage->TicketID = $ticket->id;
			$ticketMessage->save();
		}
		$this->notifyUsers($ticket, "message");
	}

	public function removeUser(Request $request, $userID, $ticketID){
		$ticket = Ticket::where('id', '=', $ticketID)->first();
		$ticket->removeUser($userID);
	}

	public function addUser(Request $request, $userID, $ticketID){
		/* @var Ticket $ticket */
		$ticket = Ticket::where('id', '=', $ticketID)->get()->first();
		$ticket->addUser($userID);
	}

	/**
	 * @param Request $request
	 * @param Ticket $id
	 */
	public function getTicketData(Request $request, Ticket $id){
		$userID = $request->user()->id;
		$result = Ticket::getTicketData($id->id);
		$result->currUserID = $userID;
		return $result;
	}

	public function getStatusArray(Request $request){
		return TicketStatus::get(['id', 'Status']);
	}

	public function getCurrentUserTickets(Request $request){
		return $this->getUserTickets($request, $request->user()->id);
}

	public function getUserTickets(Request $request, $userID){
		//return TicketUser::where('UserID', '=', $userID)->with('ticket')->get();
		$result = \DB::table('Tickets')
			->select([
				'Tickets.id as TicketID',
				'TicketStatus.Status as TicketStatus',
				'TicketPriority.Priority as TicketPriority',
				'TicketTypes.Type as TicketType',
				'Tickets.Subject as TicketSubject'

			])
			->join('TicketStatus', 'Tickets.StatusID', '=', 'TicketStatus.id')
			->join('TicketPriority', 'Tickets.PriorityID', '=', 'TicketPriority.id')
			->join('TicketTypes', 'Tickets.TypeID', '=', 'TicketTypes.id')
			->join('Ticket_User', 'Ticket_User.TicketID', '=', 'Tickets.id')
			->where('Ticket_User.UserID', '=', $userID)
			->get();
		return $result;
	}

	public function updateAttribute(Request $request, Ticket $ticket){
		$attributeName = $request->input('AttributeName');
		$attributeType = $request->input('AttributeType');
		$attribute = TicketAttribute::where('AttributeName', '=', $attributeName)->where('AttributeType', '=', $attributeType)->get()->first();
		$functionName = "ticket".$attributeType."Attributes";
		/*$ticket = Ticket::where('id', '=', $ticketID)->get()->first();*/
		$ticket->$functionName()->updateExistingPivot($attribute->id, ["AttributeValue"=>$request->input('AttributeValue')]);
	}

	public function setStatus(Request $request, Ticket $ticket, $statusID){
		//$ticket = Ticket::where('id', '=', $ticketID)->get()->first();
		$status = TicketStatus::where('id', '=', $statusID)->get()->first();
		$ticket->status()->associate($status);
		$ticket->save();
	}

	public function sendToIT(Request $request, Ticket $ticket){
		TicketToIT::firstOrCreate(['TicketID' => $ticket->id]);
		try {
			\Freshdesk::tickets()->update($ticket->FreshdeskID, ["group_id" => 9000068135]);
		}
		catch (ApiException $exception){
			echo $exception->getCode();
				echo $exception->getRequestException()->getMessage();
				echo $exception->getRequestException()->getRequest()->getBody();
			echo $exception->getMessage() . "<br><br><br>";
		}
		\Mail::send(new SendToIT($ticket->FreshdeskID));
	}

	public function setUserStatus(Request $request, Ticket $ticket){
		$status = $request->input('status');
		/* @var TicketUser $ticketUser */
		$ticketUser = TicketUser::where('TicketID', '=', $ticket->id)->get()->first();
		$ticketUser->status()->associate(TicketUserStatus::where('Status', '=', $status)->get()->first());
		$ticketUser->save();
		return $this->getTicketData($request, $ticket);
	}

	public function sendNewMessage(Request $request, Ticket $ticket){
		$message = $request->input('message');
		$function = $request->input("type") == "note" ? "note" : "reply";
		$freshdeskReply = \Freshdesk::conversations()->$function($ticket->FreshdeskID, array(
			"body" => $message
		));
		$ticketMessage = new TicketMessage();
		$ticketMessage->TicketID = $ticket->id;
		$ticketMessage->FreshdeskID = $freshdeskReply['id'];
		$ticketMessage->Message = $freshdeskReply['body'];
		$ticketMessage->FromEmail = empty($freshdeskReply['from_email'])? "Note" : $freshdeskReply['from_email'];
		$ticketMessage->save();
		return $this->getTicketData($request, $ticket);
	}
}
