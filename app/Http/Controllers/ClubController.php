<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 31/1/19
 * Time: 11:19 AM
 */

namespace App\Http\Controllers;

use App\Club;

use Illuminate\Http\Request;

class ClubController extends Controller {

	public function getClubs(Request $request)
	{
	    $clubs = Club::where('verified', true)->get();
        return response()->json($clubs);
	}

}