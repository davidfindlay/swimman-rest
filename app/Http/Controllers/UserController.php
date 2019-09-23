<?php
namespace App\Http\Controllers;
use App\JUser;
use App\User;
use App\Member;
use App\Phone;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    private $request;
    private $userId;
    private $user;

    /**
     * MemberController constructor.
     */
    public function __construct(Request $request) {
        $this->middleware('auth:api', ['except' => 'register']);
        $this->request = $request;
        $user = $this->request->user();
        if ($user != NULL) {
            $this->user = $user;
            $this->userId = intval($user->id);
        } else {
            $this->userId = NULL;
        }
    }

    /**
     * Register
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register()
    {

        if ($this->request->password == $this->request->confirmPassword) {
            $passwordHash = Hash::make($this->request->password);

            $newUserDetails = $this->request->all();
            $newUserDetails['username'] = $this->request->email;
            $newUserDetails['password'] = $passwordHash;

            $existingUser = User::where('username', '=', $newUserDetails['username'])->get();
            if (count($existingUser) > 0) {
                return response()->json([
                    'success' => false,
                    'username' => $newUserDetails['username'],
                    'message' => 'Username not available'
                ], 400);
            }

            $newUser = User::create($newUserDetails);

            return response()->json([
                'success' => true,
                'user' => $newUser], 200);

        } else {
            return response()->json([
                'success' => false,
                'message' => 'Passwords don\'t match'
            ], 400);
        }
    }

    public function linkMember($memberNumber) {
        $userDetails = $this->user;

        if ($userDetails->member != NULL) {
            return response()->json([
                'success' => false,
                'message' => 'User already matched to another member.'
            ], 400);
        }

        $matchingUser = Member::where('number', '=', $memberNumber)->first();

        if ($userDetails->surname == $matchingUser->surname &&
            $userDetails->dob == $matchingUser->dob) {
            $user = User::find($userDetails->id);
            $user->member = $matchingUser->id;

            if ($user->gender == 'M') {
                if ($matchingUser->gender != 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unable to match user'
                    ], 400);
                }
            } elseif ($user->gender == 'F') {
                if ($matchingUser->gender != 2) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unable to match user'
                    ], 400);
                }
            }

            $user->saveOrFail();

            return response()->json([
                'success' => true,
                'user' => $user
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unable to match user'
            ], 400);
        }
    }
}