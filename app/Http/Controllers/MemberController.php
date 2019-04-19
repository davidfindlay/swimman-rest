<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 31/1/19
 * Time: 11:19 AM
 */

namespace App\Http\Controllers;

use App\Member;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MemberController extends Controller {

	private $request;

	/**
	 * MemberController constructor.
	 */
	public function __construct(Request $request) {
		$this->request = $request;
	}

	public function showOneMember($id)
	{

		// $id = $request->route()[2]['id'];
		$user = $this->request->user();

		Log::info($user->member . " - " . $id);

		if ($user->member == $id) {

			$member = Member::find( $id );
			$memberships = $member->memberships;

			foreach($memberships as $m) {
				$club = $m->club;
			}

			Log::info($member);

			return response()->json( $member );

		} else {
			return response()->json([
				'error' => 'Forbidden to access this user data.'
			], 403);
		}
	}

}