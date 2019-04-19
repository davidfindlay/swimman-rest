<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 31/1/19
 * Time: 11:19 AM
 */

namespace App\Http\Controllers;

use App\Meet;
use App\MeetEvent;

use Illuminate\Http\Request;

use HTTP_Request2;
use HTTP_Request2_CookieJar;

#require_once($_SERVER["DOCUMENT_ROOT"] . "/swimman/swimman-rest/vendor/pear/http_request2/HTTP/Request2.php");
#require_once($_SERVER["DOCUMENT_ROOT"] . "/swimman/swimman-rest/vendor/pear/http_request2/HTTP/Request2/CookieJar.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/swimman/includes/html2csv.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/swimman/includes/config.php");

class SportsTGController extends Controller {

	public function getMembers(Request $request)
	{

		$uploaddir = $_SERVER["DOCUMENT_ROOT"] . '/masters-data/img';
		$uploadfile = $uploaddir . '/' . 'imgmembers.xls';
		$tempCsvFile = $uploaddir . '/' . 'temp.csv';

		#$this->getImg();

		html2csv($uploadfile, $tempCsvFile);
		$csvData = file_get_contents($tempCsvFile);

		return response($csvData, 200)->header('Content-Type', 'text/csv');
	}

	function getImg() {

		$quietmode = 1;

		$smtphost = "mail.quadrahosting.com.au";

		$portalURL = "https://console.sportstg.com/";
		$imgdir = $_SERVER["DOCUMENT_ROOT"] . "/masters-data/img";


		if ($quietmode != 1) {

			echo "Updating Membership Database from IMG...";

		}

		//addlog("batch", "getimg.php executed");

		$cookiejar = new HTTP_Request2_CookieJar();

		if ($quietmode != 1) {

			echo "Stage 1 - Login\n";

		}

		// Request 1 - Login
		$req = new HTTP_Request2($portalURL);
		$req->setConfig(array(
			'ssl_verify_peer'   => FALSE,
			'ssl_verify_host'   => FALSE
		));
		try {
			$prereq1data = $req->send()->getBody();
		} catch (HttpException $ex) {
			echo $ex;
		}

		$req = new HTTP_Request2($portalURL . "/login/index.cfm");
		$req->setMethod(HTTP_Request2::METHOD_POST);
		$username = $GLOBALS['imguser'];
		$password = $GLOBALS['imgpass'];
		$req->setCookieJar($cookiejar);

		// Add form data to request
		$req->addPostParameter(array('fuseaction' => 'Process_Validate_Login', 'Username' => "$username", 'Password' => "$password", 'Login' => 'Login'));
		$req->setConfig("follow_redirects", "false");

		$req->setConfig(array(
			'ssl_verify_peer'   => FALSE,
			'ssl_verify_host'   => FALSE
		));

		try {
			$req1data = $req->send()->getBody();

		} catch (HttpException $ex) {
			echo $ex;
		}

		// Request 2 - Get to Members Section
		if ($quietmode != 1) {

			echo "Stage 2 - Get to Members Section\n";

		}

		$req2 = new HTTP_Request2($portalURL . "/level2members/index.cfm?fuseaction=display_landing");
		$req2->setMethod(HTTP_Request2::METHOD_POST);
		$req2->setCookieJar($cookiejar);

		$req2->setConfig("follow_redirects", "false");

		$req2->setConfig(array(
			'ssl_verify_peer'   => FALSE,
			'ssl_verify_host'   => FALSE
		));

		try {
			$req2data = $req2->send()->getBody();
		} catch (HttpException $ex) {
			echo $ex;
		}

		// Request 3 - Get to Members Export Section
		if ($quietmode != 1) {

			echo "Stage 3 - Get to Members Export Page\n";

		}

		$req2 = new HTTP_Request2($portalURL . "/level2membersexport/index.cfm?fuseaction=display_landing");
		$req2->setMethod(HTTP_Request2::METHOD_POST);
		$req2->setCookieJar($cookiejar);

		$req2->setConfig("follow_redirects", "false");

		$req2->setConfig(array(
			'ssl_verify_peer'   => FALSE,
			'ssl_verify_host'   => FALSE
		));

		try {
			$req2data = $req2->send()->getBody();
		} catch (HttpException $ex) {
			echo $ex;
		}

		// Request 4 - Get to Members Export All Members
		if ($quietmode != 1) {

			echo "Stage 4 - Get to Members Export All Members Page\n";

		}

		$req2 = new HTTP_Request2($portalURL . "/level2membersexport/index.cfm?fuseaction=display_all");
		$req2->setMethod(HTTP_Request2::METHOD_POST);
		$req2->setCookieJar($cookiejar);

		$req2->setConfig("follow_redirects", "false");

		$req2->setConfig(array(
			'ssl_verify_peer'   => FALSE,
			'ssl_verify_host'   => FALSE
		));

		try {
			$req2data = $req2->send()->getBody();
		} catch (HttpException $ex) {
			echo $ex;
		}

		// Request 5 - Set details
		if ($quietmode != 1) {

			echo "Stage 5 - Set Details\n";

		}

		$req = new HTTP_Request2($portalURL . "/level2membersexport/index.cfm");
		$req->setMethod(HTTP_Request2::METHOD_POST);
		$username = $GLOBALS['imguser'];
		$password = $GLOBALS['imgpass'];
		$req->setCookieJar($cookiejar);

		// Add form data to request
		$req->addPostParameter(array('fuseaction' => 'display_all', 'TemplateID' => "0", 'Tier3Selection' => "", 'MemberListingStatus' => '2', 'MemberListingFinancialStatus' => '1'));
		$req->setConfig("follow_redirects", "false");

		$req->setConfig(array(
			'ssl_verify_peer'   => FALSE,
			'ssl_verify_host'   => FALSE
		));

		try {
			$req1data = $req->send()->getBody();

		} catch (HttpException $ex) {
			echo $ex;
		}

		// Request 6 - confirm request
		if ($quietmode != 1) {

			echo "Stage 6 - Confirm request and get file\n";

		}

		$req = new HTTP_Request2($portalURL . "/level2membersexport/index.cfm");
		$req->setMethod(HTTP_Request2::METHOD_POST);
		$username = $GLOBALS['imguser'];
		$password = $GLOBALS['imgpass'];
		$req->setCookieJar($cookiejar);

		// Add form data to request
		$req->addPostParameter(array('fuseaction' => 'Display_All_Export', 'TemplateID' => "0", 'Tier3Selection' => "", 'MemberListingStatus' => '2', 'MemberListingFinancialStatus' => '1'));
		$req->setConfig("follow_redirects", "true");

		$req->setConfig(array(
			'ssl_verify_peer'   => FALSE,
			'ssl_verify_host'   => FALSE
		));

		try {
			$req1data = $req->send()->getBody();
			file_put_contents($imgdir . "/imgmembers.xls", $req1data);
		} catch (HttpException $ex) {
			echo $ex;
		}

		//addlog("IMG Sync", "Downloaded IMG Database", "Downloaded new IMG membership database.");


	}



}