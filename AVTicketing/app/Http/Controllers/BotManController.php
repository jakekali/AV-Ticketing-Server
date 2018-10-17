<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Ticket;
use App\Http\Controllers\TicketController;

class BotManController extends Controller
{
    function handle(Request $request){
    	//$fp = fopen('H:\PHP\log.txt', 'w');
    	//fwrite($fp, json_encode($request->all()));
    	//fclose($fp);
        $returnSpeech = "Oops! Something went wrong!";
        $returnText = "OOPS SOMETHING WENT VERY WRONG!";
        $dataString = "OOPS JACOB BLEW SOMETHING UP";
        //intents logic, to action based on intent
        switch ($request->input('result')['metadata']['intentName']) {
            case "getTicketInfo";
                $user = null;
                //intent is to get information about a ticket number
                //gets ticket number from API.AI
                $ticketID = (int)$request->input('result')['parameters']['ticketID'];
                if ($request->input('originalRequest')['source'] == 'slack') {
                    $slackID = $request->input('originalRequest')['data']['user'];
                    $user = User::where('slack_id', '=', $slackID)->first();
                }


                //gets the ticket object./
                /** @var Ticket $currentTicket */
                $currentTicket = Ticket::getTicketData($ticketID);

                $dataString = $currentTicket;
                if ($currentTicket == null) {
                    $returnText = "That ticket does not exist";
                    $returnSpeech = "That ticket does not exist";
                    $dataString = $returnText;
                    break;
                }
                //echo json_encode($currentTicket);
                // echo json_encode($user);
                //var_dump($user->can('view', $currentTicket));
                if (($user != null) && (!($user->can('view', $currentTicket)))) {
                    $returnText = "I'm Sorry, You don't have permission to view this ticket";
                    $returnSpeech = "Sorry, you don't have access to view this ticket, consult ask an administrator";
                    $dataString = $returnText;
                    break;
                }

                //GET DATA FROM TICKETS -> VARS.
                $requester = $currentTicket->requester->FirstName . " " . $currentTicket->requester->LastName;
                $subject = $currentTicket->Subject;
                $description = strip_tags($currentTicket->Description);

                //For slack
                $dataString = $currentTicket->getSlackFormattedTicketArray();
                //For other clients
                $returnText = "Ticket #" . $ticketID . " was requested by " . $requester . " regarding " . $subject . ". Description: " . Ticket::cutString($description, 200) . ".";
                $returnSpeech = "Ticket #" . $ticketID . " was requested by " . $requester . " regarding " . $subject . ". Description: " . Ticket::cutString($description, 200) . ".";
                break;
            case "getTicketAssignees";
                $ticketIDs = $request->input('result')['parameters']['ticketID'];
                $user = null;
                //intent is to get information about a ticket number
                //gets ticket number from API.AI
                if ($request->input('originalRequest')['source'] == 'slack') {
                    $slackID = $request->input('originalRequest')['data']['user'];
                    $user = User::where('slack_id', '=', $slackID)->first();
                }


                $ticketError = false;
                foreach ($ticketIDs as $ticketID) {
                    $currentTicket = Ticket::getTicketData($ticketID);
                    if ($currentTicket == null) {
                        $ticketError = true;
                        $returnText = "That ticket does not exist";
                        $returnSpeech = "That ticket does not exist";
                        $dataString = $returnText;
                        break;
                    }
                }
                $canView = true;
                if($user != null) {
                    foreach ($ticketIDs as $ticketID) {
                        $currentTicket = Ticket::getTicketData($ticketID);
                        if ($user != null && !($user->can('view', $currentTicket))) {
                            $canView = false;
                        }
                    }
                }

                if($canView && !$ticketError) {
                    $returnSpeech = "Here you go: "; //. $time;
                    $returnText = "Here is the information you requested... According to Jacob's Spelling abilities: ";
                    $dataString = "Here is the info you needed: \n";
                    foreach ($ticketIDs as $ticketID) {
                        $currentTicket = Ticket::getTicketData($ticketID);
                        $ticketUsers = $currentTicket->users;
                        $returnSpeech = $returnSpeech . "Ticket #".$ticketID.":"; //. $time;
                        $returnText = $returnText . "Ticket #".$ticketID.":";
                        $dataString = $dataString . "Ticket #".$ticketID.":";
                        foreach ($ticketUsers as $ticketUser){
                            $fullName = " ".$ticketUser['firstName'] . " ". $ticketUser['lastName'] .",";
                            $returnSpeech = $returnSpeech . $fullName . " "; //. $time;
                            $returnText = $returnText . $fullName . " ";
                            $dataString = $dataString . $fullName. " ";
                            }
                        $dataString = $dataString . "\n";

                    }
                }elseif($ticketError){
                    $returnText = "I'm Sorry, that ticket doesn't exist..yet!";
                    $returnSpeech = "I'm sorry, we don't have that ticket!";
                    $dataString = $returnText;
                }elseif (!$canView){
                    $returnText = "I'm Sorry, You don't have permission to view this ticket";
                    $returnSpeech = "Sorry, you don't have access to view this ticket, consult ask an administrator";
                    $dataString = $returnText;
                }else{
                    $returnText = "Hi!";
                    $returnSpeech = "H1!";
                    $dataString = $returnText;
                }
                break;
            case "getTicketByDate";
                // $time = $request->input('result')['parameters']['date-period'];
                //intent for when user ask for tickets due at a certain time.
                $returnSpeech = "You asked me to give you information about tickets for "; //. $time;
                $returnText = $returnSpeech;
                $dataString = $returnText;
                break;

            case "assignTicket";
                //Get Information
                $ticketArray = $request->input('result')['parameters']['ticket'];
                $assigneeNames = $request->input('result')['parameters']['username'];

                //Permissions ONLY in SLACK
                if ($request->input('originalRequest')['source'] == 'slack') {
                    $slackID = $request->input('originalRequest')['data']['user'];
                    $requesterUser = User::where('slack_id', '=', $slackID)->first();
                    $canAssign = $requesterUser->can('write-users');
                    if (!$canAssign) {
                        $returnText = "You do not have the ability to assign tickets";
                        $returnSpeech = "I'm sorry, but it isn't your job to assign tickets!";
                        $dataString = $returnText;
                        break;
                    }
                }
                //SEE IF ALL DATA IS VALID
                $error = false;
                $returnText = "";
                $returnSpeech = "";
                $dataString = "";
                foreach($ticketArray as $currentTicketID){
                    $currentTicket = Ticket::getTicketData($currentTicketID);
                    if($currentTicket == null || $currentTicket == NULL){
                        $error = true;
                        $returnText = $returnText . "I'm sorry, Ticket ". $currentTicketID . " do not exist in AV Ticketing!";
                        $returnSpeech = $returnSpeech . "I'm sorry, Ticket ". $currentTicketID . " do not exist in AV Ticketing!";
                        $dataString = $dataString . "I'm sorry, Ticket ". $currentTicketID . " do not exist in AV Ticketing!";
                        break;
                    }
                }

                foreach ($assigneeNames as $assigneeName){
                    $assigneeUser = User::where('slack_id', '=', $assigneeName)->first();
                    if($assigneeUser == null ){
                        $error = true;
                        $returnText = $returnText . "I'm sorry, @". $assigneeName . " do not exist in AV Ticketing!";
                        $returnSpeech = $returnSpeech . "I'm sorry @". $assigneeName . " do not exist in AV Ticketing!";
                        $dataString = $dataString . "I'm sorry <@". $assigneeName . "> do not exist in AV Ticketing!";
                    }

                }

                if($error == false) {
                    foreach ($ticketArray as $currentTicketID) {
                        foreach ($assigneeNames as $assigneeName) {
                            $assigneeUser = User::where('slack_id', '=', $assigneeName)->first();
                                $userID = (int)$assigneeUser->id;
                                $currentTicket = Ticket::getTicketData($currentTicketID);
                          $currentTicket->addUser($userID);

                        }
                    }

                    $returnText = "The Tickets have been assigned";
                    $returnSpeech = "The following Tickets have been assigned";
                    $dataString = $returnText;
                }
                    break;

        }
    	$returnArray = array(
            "speech" => $returnSpeech,
            "displayText" => $returnText,
            "data" =>
                array("slack" => $dataString),
        );
    	return $returnArray;

    }


	public function sendMessage($userID,$message){
		//Joey: Use TicketNotification notification to send slack messages about tickets
		// Post to this URL https://hooks.slack.com/services/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

	}
}