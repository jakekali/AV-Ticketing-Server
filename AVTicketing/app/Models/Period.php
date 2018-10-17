<?php

namespace App{
/**
 * App\Period
 *
 * @property integer $id
 * @property integer $day_id
 * @property string $startTime
 * @property string $endTime
 * @property integer $periodNumber
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Day $day
 * @method static \Illuminate\Database\Query\Builder|\App\Period whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Period whereDayId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Period whereStartTime($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Period whereEndTime($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Period wherePeriodNumber($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Period whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Period whereUpdatedAt($value)
 */

	class Period extends \Eloquent
	{
		protected $fillable = ['periodNumber', 'startTime', 'endTime'];

		public function getStartTimeAttribute() {
			return date('H:i:s', strtotime($this->attributes['startTime']));
		}

		public function setStartTimeAttribute(\DateTime $value) {
			$this->attributes['startTime'] = $value->format("H:i:s");
		}

		public function getEndTimeAttribute() {
			return date('H:i:s', strtotime($this->attributes['endTime']));
		}

		public function setEndTimeAttribute(\DateTime $value) {
			$this->attributes['endTime'] = $value->format("H:i:s");
		}


		/**
		 * Returns array with period start and end times assuming that periodNumber and day are set
		 * @return array
		 */
		public function getPeriodTime(){
			$dayStartTime = date_create_from_format("H:i:s", $this->day->startTime);
			$dayEndTime = clone $dayStartTime;

			//Add correction time for Flatbush's lack of logic in period design
			$periodStartAdditionalTime = $this->getAdditionalTime($this->periodNumber-1, $this->day->day);
			$additionalTime = $this->getAdditionalTime($this->periodNumber, $this->day->day)-$periodStartAdditionalTime;
			//Add 12 minutes for Mincha + 3 minutes between periods if mincha passed
			$minchaTime = ($this->periodNumber > 8 && $this->day->day != "Wednesday" && strpos($this->day->day, "Friday") === false) || ($this->periodNumber > 9 && $this->day->day == "Wednesday") ? 15 : 0;
			//Assume that time between each period is 3 minutes
			//Period-1 because first period is at startTime
			$timePassed = new \DateInterval("PT".(($this->day->periodLength+3)*($this->periodNumber-1)+$minchaTime+$periodStartAdditionalTime)."M");
			$periodLength = new \DateInterval("PT" . ($this->day->periodLength+$additionalTime) . "M");

			return [
				$dayStartTime
					->add($timePassed),
				$dayEndTime
					->add($timePassed)
					->add($periodLength)
			];
		}

		/**
		 * Determines amount of extra time in period due to Flatbush schedule abnormalities
		 * @param $periodNumber
		 * @param $day
		 * @return integer
		 */
		public function getAdditionalTime($periodNumber, $day){
			$additionalTime = 0;
			if(($day == "Monday" || $day == "Tuesday" || $day == "Thursday") && $periodNumber > 1){
				$additionalTime += 3;
			}
			if($day == "Wednesday"){
				if($periodNumber > 8){
					$additionalTime += 1;
				}
				if($periodNumber > 11){
					$additionalTime += 1;
				}
			}
			if($day == "Friday-L" && $periodNumber > 3){
				$additionalTime += 3;
			}
			return $additionalTime;
		}

		public function day(){
			return $this->belongsTo('App\Day');
		}
	}
}

