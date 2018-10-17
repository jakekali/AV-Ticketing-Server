<?php

use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
	    $roles = array(
		    array('name'=>'admin', 'display_name'=>'Admin', 'description'=>'Admin Role', 'permissions'=>array('readAllTickets', 'writeAllTickets', 'readUsers', 'writeUsers', 'writeAdmins')),
		    array('name'=>'studentAdmin', 'display_name'=>'Student Admin', 'description'=>'Student in charge of AV', 'permissions'=>array('readAllTickets', 'writeAllTickets', 'readUsers', 'writeUsers')),
		    array('name'=>'member', 'display_name'=>'Member', 'description'=>'Regular AV Member', 'permissions'=>array('readUserTickets', 'writeUserTickets', 'readUsers')),
		    array('name'=>'trainee', 'display_name'=>'Trainee', 'description'=>'Trainee fewer permissions than AV member', 'permissions'=>array('readUserTickets'))
	    );

	    foreach($roles as $role){
		    $roleObject = \App\Role::firstOrCreate(array(
			    'name' => $role['name'],
			    'display_name' => $role['display_name'],
			    'description' => $role['description']
		    ));

		    foreach($role['permissions'] as $permission){
				$permission = \App\Permission::firstOrCreate(['name'=>$permission]);
			    if (!$roleObject->permissions->contains($permission->id)) {
				    $roleObject->permissions()->attach($permission->id);
			    }

		    }
	    }
    }
}
