<?php

use Illuminate\Database\Seeder;

class SettingsSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
	    $settings = array(
		    array('name'=>'slack_id'),
		    array('name'=>'slack_name'),
		    array('name'=>'phone_number')
	    );

	    foreach($settings as $setting){
		    $settingObject = \App\Settings::firstOrCreate(array(
			    'name' => $setting['name']
		    ));
	    }
    }
}
