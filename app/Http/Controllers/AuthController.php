<?php
namespace App\Http\Controllers;
use App\JUser;
use Validator;
use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\JWTAuth;

class AuthController extends Controller
{
	protected $jwt;

	/**
	 * Create a new controller instance.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return void
	 */
	public function __construct(JWTAuth $jwt) {
		$this->jwt = $jwt;
	}
	/**
	 * Create a new token.
	 *
	 * @param  \App\User   $user
	 * @return string
	 */
	protected function jwt(User $user) {
		$payload = [
			'iss' => "quick-entry", // Issuer of the token
			'sub' => $user->id, // Subject of the token
			'iat' => time(), // Time when JWT was issued.
			'exp' => time() + 60*60 // Expiration time
		];

		// As you can see we are passing `JWT_SECRET` as the second parameter that will
		// be used to decode the token in the future.
		return JWT::encode($payload, env('JWT_SECRET'));
	}

	public function postLogin(Request $request)
	{
		$this->validate($request, [
			'username'    => 'required',
			'password' => 'required',
		]);

		try {

			if (! $token = $this->jwt->attempt($request->only('username', 'password'))) {



				return response()->json(['user_not_found'], 404);
			}

		} catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

			return response()->json(['token_expired'], 500);

		} catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

			return response()->json(['token_invalid'], 500);

		} catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

			return response()->json(['token_absent' => $e->getMessage()], 500);

		}

		return response()->json(compact('token'));
	}

	/**
	 * Authenticate a user and return the token if the provided credentials are correct.
	 *
	 * @param  \App\User   $user
	 * @return mixed
	 */
	public function authenticate(Request $request) {

		$loginData = $request->json();

		// Find the user by email
		$user = User::where('username', $loginData->get('username'))->first();
		if (!$user) {
			// You wil probably have some sort of helpers or whatever
			// to make sure that you have the same response format for
			// differents kind of responses. But let's return the
			// below respose for now.
			Log::info('Swimman user does not exist: '. $loginData->get('username'));

			if ($this->importJUser($loginData->get('username'),
				                   $loginData->get('password'))) {

				Log::info('Imported JUser: '. $loginData->get('username'));
				$user = User::where('username', $loginData->get('username'))->first();
				return response()->json([
					'token' => $this->jwt->fromUser($user)
				], 200);

			} else {
				return response()->json( [
					'error' => 'User does not exist.'
				], 400 );
			}
		}

		// Get algorithm, hash and salt
		$hashed = str_replace(')', '', $user->passwordhash);

		if (!strpos($hashed, '(')) {
			$algo = "md5";
			$payload = $hashed;
		} else {
			list( $algo, $payload ) = explode( '(', $hashed );
		}

		list($hash, $salt) = explode(':', $payload);

		$password = $loginData->get('password');
		$testhash = hash($algo, ($password . $salt));

		// Verify the password and generate the token
		if ($testhash == $hash) {
			return response()->json([
				'token' => $this->jwt->fromUser($user),
				'user' => $user
			], 200);
		}
		// Bad Request response
		Log::info('Incorrect username or password for: '. $loginData->get('username'));
		return response()->json([
			'error' => 'Username or password is wrong.'
		], 400);
	}

	private function importJUser($username, $password) {

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

		$salt_new = base64_encode(openssl_random_pseudo_bytes(32));
		$passwordhash_new = 'sha256(' . hash('sha256', $password . $salt_new) . ':' . $salt_new . ')';

		$newUser = new User;

		$memberLink = $jUser->jUserLinks->last();

		if (isset($memberLink)) {
			$member = $memberLink->member;
			$newUser->member = $member['id'];
			$newUser->firstname = $member['firstname'];
			$newUser->surname = $member['surname'];
		}

		$newUser->username = $username;
		$newUser->passwordhash = $passwordhash_new;

		$newUser->save();

		return true;
	}
}