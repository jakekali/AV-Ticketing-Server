<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function sendResetResponse($response)
    {
	    return array("response" => trans($response), "type" => "Success");
    }

    public function sendResetFailedResponse(Request $request, $response)
    {
    	return array("response" => trans($response), "type" => "Error");
    }

	public function validateToken(Request $request){
    	if($request->exists('token')){
    		$result = \DB::table('password_resets')->where('token', '=', $request->input('token'))->get();
    		if($result->count() > 0){
    			return array(
    				"email" => $result[0]->email
		        );
		    }
	    }
	    return null;
    }
}
