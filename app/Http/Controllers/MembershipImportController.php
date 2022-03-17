<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 31/1/19
 * Time: 11:19 AM
 */

namespace App\Http\Controllers;

use App\MembershipImport;
use App\MembershipImportLog;
use App\SportsTGMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use DB;

class MembershipImportController extends Controller
{

    private $request;
    private $userId;
    private $user;

    /**
     * MemberController constructor.
     */
    public function __construct(Request $request) {
        $this->request = $request;
        $user = $this->request->user();
        if ($user != NULL) {
            $this->user = $user;
            $this->userId = intval($user->id);
        } else {
            $this->userId = NULL;
        }
    }

    public function isAdmin() {
        if ($this->user && $this->user->is_admin) {
            return true;
        }
        return false;
    }

    public function getImports() {
        if ($this->isAdmin()) {

            $membershipImports = MembershipImport::orderBy('id', 'desc')->get();

            return response()->json([
                'success' => true,
                'membershipImports' => $membershipImports]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden to access membership import data.'
            ], 403);
        }
    }

    public function autoImport() {
        if ($this->isAdmin()) {
            $importRun = new MembershipImport();
            $importRun->requested_at = date('Y-m-d H:i:s');
            $importRun->started_at = date('Y-m-d H:i:s');
            $importRun->source = 'admin';
            $importRun->user_id = $this->userId;

            $importRun->saveOrFail();

            $importRun->refresh();

            $importId = $importRun->id;

            $client = new Client([
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-api-key' => 'FsejMPugAJ3P0iWbtmyKx69vrppEaoTT28Hu35KF'
                ]
            ]);

            $response = $client->post(env('SPORTSTG_IMPORTER'),
                ['body' => json_encode(
                    [
                        'importId' => $importId
                    ]
                )]
            );

            return response()->json([
                'success' => true,
                'message' => 'Successfully requested automatic membership import.']);
        }
    }

    public function importLogMessage() {
        $logData = $this->request->all();

        $logMessage = new MembershipImportLog();
        $logMessage->import_id = $logData->importId;
        $logMessage->message = $logData->message;
        $logMessage->created_at = time();

        try {
            $logMessage->saveOrFail();

            return response()->json([
                'success' => true,
                'logMessage' => $logMessage]);
        } catch (Exception $e) {
            Log::error('Unable to save log message: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    public function importMembershipFileRow() {
        $memberData = $this->request->all();

        // See if a row exists for this external ID already
        $memberRow = SportsTGMember::where('external_id', '=', $memberData->externalId)->first();

        // If member row not found
        if ($memberRow == NULL) {
            $memberRow = new SportsTGMember();
            $memberRow->external_id = $memberData->externalId;
        }

        $memberRow->number = $memberData->number;
        $memberRow->surname = $memberData->surname;
        $memberRow->first_name = $memberData->firstName;
        $memberRow->member_data = $memberData->data;
        $memberRow->saveOrFail();

        return response()->json([
            'success' => true,
            'memberData' => $memberRow]);
    }

}