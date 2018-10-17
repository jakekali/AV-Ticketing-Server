<?php

namespace App\Http\Controllers;

use App\ClassInstance;
use App\Day;
use App\Period;
use App\Role;
use App\Room;
use App\SchoolClass;
use App\User;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;
use Eluceo\iCal\Component\TimezoneRule;
use Eluceo\iCal\Property\Event\RecurrenceRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
	public function insertPeriod(Request $request){
		/** @var Day $day */
		$day = Day::firstOrCreate(["day"=>"Monday"]);

		$periodNumber = 1;
		$startTime = (new \DateTime())->setTime(8,15);
		$endTime = (new \DateTime())->setTime(8,57);

		$period = Period
			::where([
				'periodNumber'=>$periodNumber,
				"startTime"=>$startTime,
				"endTime"=>$endTime
			])
			->whereDayId($day->id)
			->with('day')
			->get();
		if($period->count() > 0){
			return $period->first();
		}
		else{
			/** @var Period $period */
			$period = new Period();
			$period->periodNumber = $periodNumber;
			$period->startTime = $startTime;
			$period->endTime = $endTime;
			$period->day()->associate($day);
			$period->save();
			return $period->getAttributes();
		}
	}

	public function uploadSchedule(Request $request){
		if($request->hasFile('schedule')) {
			$validator = \Validator::make([
				'schedule' => $request->file('schedule')->getClientOriginalExtension()
			], [
				'schedule' => 'in:csv'
			], [
				'in' => 'The uploaded schedule isn\'t a CSV'
			]);
			if($validator->errors()->count() > 0){
				return $validator->errors()->all();
			}
			$scheduleCSV = $request->file('schedule');
			$fp = $scheduleCSV->openFile();
			$fp->fgetcsv();
			$lines = array();
			$classes = array();
			while (!$fp->eof()) {
				$line = $fp->fgetcsv();
				array_push($lines, $line);
				$day = $line[0];
				for ($period = 1; $period < count($line); $period += 1) {
					if(!empty($line[$period])) {
						list($class, $room) = explode(":", $line[$period]);
						$classes[$class."-".$day."-".$period."-".$room] = 1;
					}
				}
			}
			$this->expireOldClasses($this->getSchedule($request->user()->id,
				(new \DateTime())->format('mdY') , "internal"), $classes);
			foreach ($lines as $line) {
				$day = $line[0];
				for ($period = 1; $period < count($line); $period += 1) {
					if(!empty($line[$period])) {
						list($class, $room) = explode(":", $line[$period]);
						$this->insertClassInstance($class, $room, $day, $period, $request->user());
					}
				}
			}
			return null;
		}
		else{
			return ["File Not Uploaded"];
		}
	}

	private function expireOldClasses($existingClasses, $newClasses){
		foreach ($existingClasses as $existingClass=>$pivotID) {
			if(!array_key_exists($existingClass, $newClasses)){
				//echo "Expiring " . $existingClass . " ID: " . $pivotID;
				\DB::table('user_class')
					->where('id', $pivotID)
					->update(['date_expired'=>date('Y-m-d')]);
			}
			else{
				//echo $existingClass . " already exists <br>\r\n";
			}
		}
	}

	public function getScheduleFromAPI(Request $request, $id, $startDate){
		return $this->getSchedule($id, $startDate, "API");
	}

	public function getSchedule($id, $startDate, $source){
		$tz  = 'America/New_York';
		$startDate = $this->getDateMonday(date_create_from_format('mdY', $startDate));
		$startDate->setTimezone(new \DateTimeZone($tz));
		$days = array(
			"Monday"=>array(
				'date' => $startDate->format('m/d/Y')
			),
			"Tuesday"=>array(
				'date' => date_add($startDate, new \DateInterval('P1D'))->format('m/d/Y')
			),
			"Wednesday"=>array(
				'date' => date_add($startDate, new \DateInterval('P1D'))->format('m/d/Y')
			),
			"Thursday" => array(
				'date' => date_add($startDate, new \DateInterval('P1D'))->format('m/d/Y')
			),
			"Friday" => array(
				'date' => date_add($startDate, new \DateInterval('P1D'))->format('m/d/Y'),
				'isDaylightSavings' => (int)$startDate->format('I')
			));
		$queryStartDate = date_create_from_format('m/d/Y', $days['Monday']['date']);
		$queryEndDate = date_create_from_format('m/d/Y', $days['Friday']['date']);

		/*\DB::enableQueryLog();*/

		$classes = User::whereId($id)
			->with(array(
					'classInstances' => function($query) use ($queryStartDate, $queryEndDate){
						$query->where('date_added', '<=', $queryStartDate);
						$query->where(function($query) use($queryEndDate){
							$query->where('date_expired', '>', $queryEndDate)
								->orWhereNull('date_expired');
						});
					})
			)->get()[0]->classInstances;
		$noOtherClasses = false;
		if(count($classes) == 0){
			$classes = User::whereId($id)
				->with(array(
						'classInstances' => function($query) use ($queryStartDate, $queryEndDate){
							$query->whereNull('date_expired');
						})
				)->get()[0]->classInstances;

			$noOtherClasses = true;
		}
		$parsedClasses = array();
		/*echo json_encode(\DB::getQueryLog());*/
		foreach ($classes as $class) {
			$day = $class->period->day->day;
			if(
				($day != "Friday-L" && $day != "Friday-S") ||
				($day == "Friday-L" && $days['Friday']['isDaylightSavings'] == 1) ||
				($day == "Friday-S" && $days['Friday']['isDaylightSavings'] == 0)
			) {

				if ($day == "Friday-L" || $day == "Friday-S")
					$day = "Friday";

				if (
					( (date_create_from_format('m/d/Y', $days[$day]['date']) < new \DateTime($class->pivot->date_expired) || empty($class->pivot->date_expired) )
					&& date_create_from_format('m/d/Y', $days[$day]['date']) >= new \DateTime($class->pivot->date_added)
					) || $noOtherClasses == true
				)
				{

					$startTime = $days[$day]['date'] . ' ' . $class->period->startTime;
					$endTime = $days[$day]['date'] . ' ' . $class->period->endTime;

					if ($source == "API") {
						array_push($parsedClasses, array(
							"id" => $class->id,
							"title" => $class->schoolClass->ClassName . ' - ' . $class->room->RoomName,
							"allDay" => false,
							"start" => date_create_from_format('m/d/Y H:i:s', $startTime)->format('c'),
							"end" => date_create_from_format('m/d/Y H:i:s', $endTime)->add(new \DateInterval('PT1S'))->format('c')
						));
					}
					elseif ($source == "export") {
						array_push($parsedClasses, array(
							"id" => $class->id,
							"title" => $class->schoolClass->ClassName,
							"location" => $class->room->RoomName,
							"allDay" => false,
							"start" => date_create_from_format('m/d/Y H:i:s', $startTime),
							"end" => date_create_from_format('m/d/Y H:i:s', $endTime)

						));
					}
					elseif ($source == "internal") {
						$parsedClasses[$class->schoolClass->ClassName . "-" . $day . "-" . $class->period->periodNumber . "-" . $class->room->RoomName] = $class->pivot->id;
					}
				}
			}
		}
		return $parsedClasses;
	}

	public function exportSchedule(Request $request){
		$tz  = 'America/New_York';
		date_default_timezone_set($tz);

		$currDate = (new \DateTime());
		$currDate = date_create_from_format('m/d/Y', '09/01/2016');
		$schedules = array();
		$schedules['start'] =
			array(
				"start" => $currDate->format('m/d/Y'),
				"schedule" => $this->getSchedule($request->user()->id, $currDate->format('mdY'), "export")
			);
//		echo json_encode($this->getSchedule($request->user()->id, $currDate->format('mdY'), "export"));

		if($currDate->format('I') == 1 && $currDate->format('m') > 8){
			while($currDate->format('I') == 1){
				$currDate->add((new \DateInterval('P7D')));
			}
			$schedules['nodst'] =
				array(
					"start" => $currDate->format('m/d/Y'),
					"schedule" => $this->getSchedule($request->user()->id, $currDate->format('mdY'), "export")
				);
		}
		if($currDate->format('I') == 0){
			while($currDate->format('I') == 0){
				$currDate->add((new \DateInterval('P7D')));
			}
			$schedules['dst'] =
				array(
					"start" => $currDate->format('m/d/Y'),
					"schedule" => $this->getSchedule($request->user()->id, $currDate->format('mdY'), "export")
				);
		}

		$ical = new Calendar("AV Ticketing");
		//echo json_encode($schedules);
		foreach ($schedules as $name=>$schedule) {
			$dstToggleDate = $this->getDstToggle(date_create_from_format('m/d/Y', $schedule['start']));
			$repetitionRule = (new RecurrenceRule())
								->setFreq(RecurrenceRule::FREQ_WEEKLY)
								->setInterval(1)
								->setUntil($dstToggleDate);
			foreach ($schedule['schedule'] as $class) {
				$event = (new Event());
				$event
					->setDtStart($class['start'])
					->setDtEnd($class['end'])
					->setLocation($class['location']);
				$event
					->setSummary($class['title'])
					->addRecurrenceRule($repetitionRule)
					->setUseTimezone(true);
				$ical->addComponent($event);
			}
		}
		$ical->setName("School Schedule");
		header('Content-Type: text/calendar; charset=utf-8');
		header('Content-Disposition: attachment; filename="cal.ics"');
		echo $ical->render();
	}

	private function getDstToggle(\DateTime $startDate){
		$start = $startDate->format('I');
		while($startDate->format('I') == $start){
			$startDate->add(new \DateInterval('P1D'));
		}
		return $startDate;
	}

	/**
	 * @param \DateTime $dateTime
	 * @return \DateTime $mondayDate
	 */
	private function getDateMonday(\DateTime $dateTime){
		return date_create_from_format('U', strtotime('monday this week', $dateTime->format('U')));
	}

	private function insertClassInstance($class, $room, $day, $period, User $user){
		if($day == "Friday"){
			$day = "Friday-S";
			$this->insertClassInstance($class, $room, "Friday-L", $period, $user);
		}
		$schoolClassObject = SchoolClass::firstOrCreate(["ClassName"=>$class]);
		$roomObject = Room::firstOrCreate(["RoomName"=>$room]);
		$dayObject = Day::where(["day"=>$day])->first();

		/** @var Period $periodObject */
		$periodObject = Period::firstOrNew([
			"day_id" => $dayObject->id,
			"periodNumber" => $period
		]);
		//If this period is new then insert it
		if(!$periodObject->exists){
			$periodObject->day()->associate($dayObject);
			$periodObject->startTime = $periodObject->getPeriodTime()[0];
			$periodObject->endTime = $periodObject->getPeriodTime()[1];
			$periodObject->save();
		}

		/** @var ClassInstance $classInstance */
		$classInstance = ClassInstance::where([
			"schoolClassID" => $schoolClassObject->id,
			"roomID" => $roomObject->id,
			"periodID" => $periodObject->id
		]);

		if($classInstance->count() == 0){
			$classInstance = new ClassInstance();
			$classInstance->schoolClass()->associate($schoolClassObject);
			$classInstance->room()->associate($roomObject);
			$classInstance->period()->associate($periodObject);
			$classInstance->save();
		}
		else{
			$classInstance = $classInstance->first();
		}

		if(!$user->classInstances->contains($classInstance->id)) {
			$user->classInstances()->attach($classInstance->id, ['date_added' => date('Y-m-d')]);
		}


	}
}
