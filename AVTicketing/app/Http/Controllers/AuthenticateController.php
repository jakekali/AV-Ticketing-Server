<?php

namespace App\Http\Controllers;



use App\User;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthenticateController extends Controller
{
	public function authenticate(Request $request){
		$credentials = array(
			"email" => $request->input('email'),
			"password" => $request->input("password")
		);
		try {
			// attempt to verify the credentials and create a token for the user
			if (! $token = JWTAuth::attempt($credentials)) {
				return response()->json(['error' => 'invalid_credentials'], 401);
			}
		} catch (JWTException $e) {
			// something went wrong whilst attempting to encode the token
			return response()->json(['error' => 'could_not_create_token'], 500);
		}
		return response()->json(
			compact('token')
		);
	}

	public function register(Request $request){
		$user = new User();
		$user->firstName = $request->input("firstName");
		$user->lastName = $request->input("lastName");
		$user->email = $request->input("email");
		$user->password = $this->hashPassword($request->input("password"));
		$user->api_token = str_random(60);
		$user->save();
		$user->roles()->attach($request->input("roleID"));
	}


	private function hashPassword($password){
		return bcrypt($password);
	}

}
