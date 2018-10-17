<?php

use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new \App\User();
        $user->firstName = "Admin";
        $user->lastName = "Admin";
        $user->email = "admin@admin.com";
        $user->password = bcrypt("admin");
        $user->api_token = str_random(60);
        $user->save();
        $adminID = \App\Role::whereDisplayName('Admin')->first()->id;
        $user->roles()->attach($adminID);

    }
}
