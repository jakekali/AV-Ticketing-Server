<?php

namespace App\Http\Controllers;

use App\Permission;
use App\Role;
use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
	public function addRole(Request $request, $userID, $roleID){
		/** @var Role $role */
		$role = Role::where('id', '=', $roleID)->first();
		/** @var User $user */
		$user = User::where('id', '=', $userID)->first();
		if(strpos(strtolower($role->name), "admin") === FALSE || $request->user()->hasPermission("writeAdmins")) {
			$user = User::where('id', '=', $userID)->with('roles')->first();
			$user->roles()->attach($roleID);
			return User::where('id', '=', $userID)->with('roles')->first();
		}
		return User::where('id', '=', $userID)->with('roles')->first();
	}

	public function removeRole(Request $request, $userID, $roleID){
		/** @var Role $role */
		$role = Role::where('id', '=', $roleID)->first();
		/** @var User $user */
		$user = User::where('id', '=', $userID)->first();
		if(strpos(strtolower($role->name), "admin") === FALSE || $request->user()->hasPermission("writeAdmins")) {
			$user = User::where('id', '=', $userID)->with('roles')->first();
			$user->roles()->detach($roleID);
			return User::where('id', '=', $userID)->with('roles')->first();
		}
		return User::where('id', '=', $userID)->with('roles')->first();
	}

	public function getRoles(Request $request){
		/** @var User $user */
		$user = $request->user();
		return $user->roles;
	}

	/**
	 * @param Request $request
	 * @param $id - user id or -1 for current user
	 * @return mixed
	 */
	public function getUserData(Request $request, $id){
		if($id == -1)
			$id = $request->user()->id;
		return User::whereId($id)
			->with('roles')
			->get()[0];
	}


	public function getRoleEnum(Request $request){
		/** @var User $user */
		$user = $request->user();
		if($user->hasPermission("writeAdmins"))
			return Role::all();
		elseif ($user->hasPermission("writeUsers")){
			return Role::where('name', 'NOT LIKE', '%Admin%')->get();
		}
		return [];
	}

	public function searchAvMembers(Request $request, $searchText){
		$searchText = str_replace("*", "", $searchText);
		$searchValues = preg_split('/\s+/', $searchText);

		return User::where(function ($q) use ($searchValues) {
			foreach ($searchValues as $value) {
				$q->orWhere('firstName', 'like', "%{$value}%");
				$q->orWhere('lastName', 'like', "%{$value}%");
			}
		})->get();
	}

	public function syncWithSlack(Request $request){
		$requestUrl = "https://slack.com/api/users.list?token=".env("SLACK_TOKEN");
		$result = json_decode(file_get_contents($requestUrl));
		foreach ($result->members as $member) {
			if(isset($member->profile->email)) {
				$email = $member->profile->email;
				$slackID = $member->id;
				$name = $member->name;
				$user = User::where('email', '=', $email)->get();
				if (count($user) > 0) {
					//Todo: Remove Slack_id and slack_name
					$user[0]->slack_id = $slackID;
					$user[0]->slack_name = $name;
					$user[0]->updateSetting("slack_id", $slackID);
					$user[0]->updateSetting("slack_name", $name);
					$user[0]->save();
				}
			}
		}
		return;
	}

	public function updateSettings(Request $request){
		$settings = $request->input("settings");
		foreach ($settings as $setting=>$value){
			/** @var User $user */
			$user = $request->user();
			$user->updateSetting($setting, $value);
		}
	}

	public function getSettings(Request $request){
		return $request->user()->settings;
	}

	public function updatePassword(Request $request){
		/** @var User $user */
		$user = $request->user();
		if(\Auth::validate(['email' => $user->email, 'password' => $request->input('oldPassword')])){
			$user->password = bcrypt($request->input('newPassword'));
			$user->save();
			return array("result" => "Password has been changed", "type" => "Success");
		}
		else{
			return array("result" => "Existing password isn't valid", "type" => "Error");
		}
	}
	
	public function updateUserInfo(Request $request){
		$user = $request->user();
		$user->firstName = !empty($request->input('firstName')) ? $request->input('firstName') : $user->firstName;
		$user->lastName = !empty($request->input('lastName')) ? $request->input('lastName') : $user->lastName;
		$user->email = !empty($request->input('email')) ? $request->input('email') : $user->email;
		$user->save();
	}
}
