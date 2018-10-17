<?php

use Illuminate\Database\Seeder;

class DaysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $days = [
	        ['day'=>'Monday', 'periodLength'=>42, 'startTime'=> (new DateTime())->setTime(8,15)],
	        ['day'=>'Tuesday', 'periodLength'=>42, 'startTime'=> (new DateTime())->setTime(8,15)],
	        ['day'=>'Wednesday', 'periodLength'=>35, 'startTime'=> (new DateTime())->setTime(8,10)],
	        ['day'=>'Thursday', 'periodLength'=>42, 'startTime'=> (new DateTime())->setTime(8,15)],
	        ['day'=>'Friday-S', 'periodLength'=>28, 'startTime'=> (new DateTime())->setTime(8,10)],
	        ['day'=>'Friday-L', 'periodLength'=>30, 'startTime'=> (new DateTime())->setTime(8,10)],
        ];

	    foreach($days as $day){
		    \App\Day::firstOrCreate($day);
	    }
    }
}
