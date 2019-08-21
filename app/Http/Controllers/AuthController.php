<?php
namespace App\Http\Controllers;
use App\JUser;
use App\User;
use App\Phone;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

	/**
	 * Create a new controller instance.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return void
	 */
	public function __construct() {
		$this->middleware('auth:api', ['except' => 'login']);
	}

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only(['username', 'password']);


        if (! $token = $this->guard()->attempt($credentials)) {

            if (! $this->importJUser($credentials)) {
                return response()->json(['error' => 'Unnable to import user'], 401);
            } else {
                $token = $this->guard()->attempt($credentials);
            }
        }

        return $this->respondWithToken($token);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->guard()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60,
            'user' => $this->guard()->user()
        ]);
    }

    public function guard() {
        return Auth::guard('api');
    }

    private function importJUser($credentials) {

        $username = $credentials['username'];
        $password = $credentials['password'];

        $existingUser = User::where('username', $username)->first();
        if ($existingUser) {
            return false;
        }

        $existingUser = User::where('email', $username)->first();
        if ($existingUser) {
            return false;
        }

		$jUser = JUser::where('username', $username)->first();

		if ( isset($jUser) ) {

			list( $md5pass, $md5salt ) = explode( ':', $jUser['password'] );
			$userhash = md5( $password . $md5salt );

			if ( $md5pass != $userhash ) {

				header( 'HTTP/1.1 401 Unauthorized', true, 401 );
				Log::error( "Authentication failure for $username" );
				return false;

			} else {

				Log::info( "User login by $username via Joomla user" );

			}

		} else {
			header( 'HTTP/1.1 401 Unauthorized', true, 401 );
			Log::error( "User profile not found for $username" );
			return false;
		}

		// If they're authenticated with Joomla, get the Joomla email address
		$email = $jUser['email'];
        $newPassword = Hash::make($password);

		$newUser = new User;

		$memberLink = $jUser->jUserLinks->last();

		if (isset($memberLink)) {
			$member = $memberLink->member;
			$newUser->member = $member['id'];
			$newUser->firstname = $member['firstname'];
			$newUser->surname = $member['surname'];

			if ($member['gender'] == 1) {
			    $newUser->gender = 'M';
            } else {
			    $newUser->gender = 'F';
            }

			$newUser->dob = $member['dob'];

		}

		$newUser->username = $username;
		$newUser->email = $email;
		$newUser->password = $newPassword;

		// Get most recent phone number
		$newPhones = $member['phones'];
		$numPhones = count($newPhones);
		$newUser->phone = $newPhones[$numPhones - 1]['phonenumber'];

		// Get most recent emergency contact
        $newEmergency = $member['emergency'];

        $newUser->emergency_firstname = $newEmergency->firstname;
        $newUser->emergency_surname = $newEmergency->surname;
        $newUser->emergency_phone = $newEmergency->phone->phonenumber;
        $newUser->emergency_email = $newEmergency->email;

		$newUser->save();

		return true;
	}
}