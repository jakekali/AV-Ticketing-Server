<?php

namespace App{
/**
 * App\ClassInstance
 *
 * @property-read \App\SchoolClass $schoolClass
 * @property-read \App\Room $room
 * @property-read \App\Period $period
 */

	class ClassInstance extends \Eloquent
	{
		protected $table = "classInstances";
		public function schoolClass(){
			return $this->belongsTo('App\SchoolClass', 'schoolClassID', 'id');
		}

		public function room(){
			return $this->belongsTo('App\Room', 'roomID', 'id');
		}

		public function period(){
			return $this->belongsTo('App\Period', 'periodID', 'id');
		}


	}
}

