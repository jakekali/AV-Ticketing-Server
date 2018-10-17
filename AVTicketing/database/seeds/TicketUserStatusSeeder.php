<?php

use Illuminate\Database\Seeder;

class TicketUserStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
	    $statusArray = [
		    ['Status'=>'Assigned'],
		    ['Status'=>'Rejected'],
		    ['Status'=>'Accepted']
	    ];

	    foreach($statusArray as $status){
		    \App\TicketUserStatus::firstOrCreate($status);
	    }
    }
}
