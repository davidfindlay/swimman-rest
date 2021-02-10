<?php

namespace App\Http\Controllers;

use App\JUser;
use App\PasswordGenerationWord;
use App\PasswordResetToken;
use App\User;
use App\Phone;
use mysql_xdevapi\Exception;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'resetPassword', 'verifyResetPasswordToken',
            'resetPasswordToken']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only(['username', 'password']);

        if (!$token = $this->guard()->attempt($credentials)) {

            if (!$this->importJUser($credentials)) {
                return response()->json(['error' => 'Unable to import user'], 401);
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
     * @param string $token
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

    public function guard()
    {
        return Auth::guard('api');
    }

    private function importJUser($credentials)
    {

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

        if (isset($jUser)) {

            list($md5pass, $md5salt) = explode(':', $jUser['password']);
            $userhash = md5($password . $md5salt);

            if ($md5pass != $userhash) {

                header('HTTP/1.1 401 Unauthorized', true, 401);
                Log::error("Authentication failure for $username");
                return false;

            } else {

                Log::info("User login by $username via Joomla user");

            }

        } else {
            header('HTTP/1.1 401 Unauthorized', true, 401);
            Log::error("User profile not found for $username");
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

        }

        $newUser->username = $username;
        $newUser->email = $email;
        $newUser->password = $newPassword;

        $newUser->save();

        return true;
    }

    public function resetPassword($emailAddress, Request $request)
    {
        $existingUser = User::where('email', $emailAddress)->first();

        if ($existingUser == NULL) {
            return response()->json([
                'success' => false,
                'message' => 'No user found with that email address!'], 200);
        }

        $admin_details = $request->only(['user_id']);
        $adminName = '';
        $adminUser = 0;
        if ($admin_details != NULL) {
            if (array_key_exists('user_id', $admin_details)) {
                $adminUser = User::find($admin_details['user_id']);

                if ($adminUser != NULL && $adminUser->is_admin) {
                    $adminName = $adminUser->firstname . ' ' . $adminUser->surname;
                }
            }
        }

        $userDisplayName = $existingUser->firstname . ' ' . $existingUser->surname;

        $token = $this->random_str(8);

        $passwordReset = new PasswordResetToken();
        $passwordReset->user_id = $existingUser->id;
        $passwordReset->token = $token;
        $passwordReset->valid_till = date("Y-m-d H:i:s", time() + 86400);
        $passwordReset->used = false;

        $passwordReset->save();

        if ($adminName != '') {
            $data = array('resetLink' => env('SITE_BASE') . '/reset-password/' . $token,
                'adminName' => $adminName);
            Mail::send('resetpasswordadmin', $data, function ($message) use ($emailAddress, $userDisplayName) {
                $message->to($emailAddress, $userDisplayName)->subject('Password Reset Request');
                $message->from('recorder@mastersswimmingqld.org.au', 'MSQ Quick Entry');
            });
        } else {
            $data = array('resetLink' => env('SITE_BASE') . '/reset-password/' . $token);
            Mail::send('resetpassword', $data, function ($message) use ($emailAddress, $userDisplayName) {
                $message->to($emailAddress, $userDisplayName)->subject('Password Reset Request');
                $message->from('recorder@mastersswimmingqld.org.au', 'MSQ Quick Entry');
            });
        }

        Log::info("Password reset request for " . $existingUser->username . " sent to " . $emailAddress . '.');


        return response()->json([
            'adminDetails' => $admin_details,
            'adminUser' => $adminUser,
            'success' => true], 200);

    }

    public function verifyResetPasswordToken($token) {
        $passwordToken = PasswordResetToken::where('token', $token)->first();

        if ($passwordToken == NULL) {
            return response()->json([
               'success' => true,
               'token' => $token,
               'valid' => false
            ]);
        }

        if ($passwordToken->used) {
            return response()->json([
                'success' => true,
                'token' => $token,
                'valid' => false
            ]);
        }

        if (strtotime($passwordToken->valid_till) < time()) {
            return response()->json([
                'success' => true,
                'token' => $token,
                'valid' => false
            ]);
        }

        return response()->json([
            'success' => true,
            'token' => $token,
            'valid' => true
        ]);

    }

    public function resetPasswordToken($token, Request $request) {
        $passwordToken = PasswordResetToken::where('token', $token)->first();

        if ($passwordToken == NULL) {
            return response()->json([
                'success' => true,
                'token' => $token,
                'valid' => false
            ]);
        }

        if ($passwordToken->used) {
            return response()->json([
                'success' => true,
                'token' => $token,
                'valid' => false
            ]);
        }

        if (strtotime($passwordToken->valid_till) < time()) {
            return response()->json([
                'success' => true,
                'token' => $token,
                'valid' => false
            ]);
        }

        $user = User::find($passwordToken->user_id);

        if ($user != NULL) {
            $newPassword = $request->only(['newPassword']);

            if (!array_key_exists('newPassword', $newPassword)) {
                return response()->json([
                    'success' => false,
                    'token' => $token,
                    'valid' => true
                ]);
            }

            $newPasswordString = $newPassword['newPassword'];
            $passwordHash = Hash::make($newPasswordString);
            $user->password = $passwordHash;
            $user->save();

            $passwordToken->used = true;
            $passwordToken->save();

            return response()->json([
                'success' => true,
                'token' => $token,
                'valid' => true
            ]);

        }

    }

    public function changePassword($userId, Request $request) {
        $user = User::find($userId);

        $requestUser = $request->user();

        if (!$requestUser->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Permission denied. You must be an admin to perform this action'
            ], 403);
        }

        $user->password = Hash::make($request->newPassword);
        $user->save();

        $adminName = $requestUser->firstname . ' ' . $requestUser->surname;
        $userDisplayName = $user->firstname . ' ' . $user->surname;
        $emailAddress = $user->email;

        $data = array('newPassword' => $request->newPassword, 'adminName' => $adminName);
        Mail::send('changepasswordadmin', $data, function ($message) use ($emailAddress, $userDisplayName) {
            $message->to($emailAddress, $userDisplayName)->subject('Password Changed');
            $message->from('recorder@mastersswimmingqld.org.au', 'MSQ Quick Entry');
        });

        return response()->json([
            'success' => true,
            'message' => 'Password succesfully changed.'
        ]);
    }

    function random_str(
        $length = 64,
        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyz') {
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        try {
            for ($i = 0; $i < $length; ++$i) {
                $pieces [] = $keyspace[random_int(0, $max)];
            }
        } catch (Exception $e) {
            return null;
        }
        return implode('', $pieces);
    }

    public function generateSimplePassword() {
        $words = PasswordGenerationWord::all()->toArray();

        $selectedWordItem = $words[array_rand($words)];
        $passwordNumber = random_int(10, 99);

        $generatedPassword = $selectedWordItem['word'] . $passwordNumber;

        return response()->json([
            'success' => true,
            'words' => $selectedWordItem,
            'password' => $generatedPassword
        ]);
    }
}