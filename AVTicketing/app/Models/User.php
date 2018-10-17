<?php

namespace App{
/**
 * App\User
 *
 * @property integer $id
 * @property string $firstName
 * @property string $lastName
 * @property string $email
 * @property string $password
 * @property string $remember_token
 * @property string $api_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Role[] $roles
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ClassInstance[] $classInstances
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $unreadNotifications
 * @method static \Illuminate\Database\Query\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereFirstName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereLastName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User whereUpdatedAt($value)
 */
	use App\Notifications\AngularResetPassword;
	use Illuminate\Auth\Passwords\CanResetPassword;
	use Illuminate\Notifications\Notifiable;
	use Illuminate\Foundation\Auth\User as Authenticatable;

	class User extends Authenticatable
	{
		use Notifiable;
		use CanResetPassword;

		protected $fillable = [
			'id','firstName', 'lastName', 'email', 'password', 'api_token', 'slack_id', 'slack_name'
		];

		protected $hidden = [
			'password', 'remember_token', 'api_token'
		];

		public function roles(){
			return $this->belongsToMany('App\Role', 'user_role', 'user_id', 'role_id');
		}

		/**
		 * @return array
		 */
		public static function getAdmins(){
			$returnArray = array();
			foreach(User::all() as $user){
				foreach ($user->roles as $role) {
					if($role->name == "admin" || $role->name == "studentAdmin"){
						array_push($returnArray, $user);
					}
				}
			}
			return $returnArray;
		}

		public function classInstances(){
			return $this->belongsToMany('App\ClassInstance', 'user_class', 'user_id', 'classInstance_id')
				->withPivot(['date_added', 'date_expired', 'id']);
		}

		public function settings(){
			return $this->belongsToMany('App\Settings', 'user_settings', 'user_id', 'setting_id')
				->withPivot(['value']);
		}

		public function tickets(){
			return $this->hasMany('App\TicketUser', 'UserID');
		}

		public function hasPermission($permissionName){
			$roles = $this->roles;
			foreach ($roles as $role) {
				foreach ($role->permissions as $permission) {
					if ($permission->name == $permissionName) {
						return true;
					}
				}
			}
			return false;
		}

		/**
		 * Route notifications for the Slack channel.
		 *
		 * @return string
		 */
		public function routeNotificationForSlack()
		{
//			return "https://hooks.slack.com/services/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";
			return "https://slack.com/api/chat.postMessage";
		}

		public function updateSetting($setting, $value){
			$existingPivot = $this->settings();
			$settingID = Settings::where('name', '=', $setting)->first()['id'];
			if (!$this->settings->contains($settingID)) {
				$this->settings()->attach($settingID, ['value'=>$value]);
			}
			else{
				$this->settings()->updateExistingPivot($settingID, ['value'=>$value]);
			}
		}

		public function getSetting($setting){
			foreach ($this->settings as $settingIter) {
				if($settingIter->name == $setting)
					return $settingIter->pivot->value;
			}
			return null;
		}

		public function sendPasswordResetNotification($token)
		{
			$this->notify(new AngularResetPassword($token));
		}
	}

}

