<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 31/1/19
 * Time: 11:19 AM
 */

namespace App\Http\Controllers;

use App\Meet;
use App\MeetEntry;
use App\MeetEntryIncomplete;
use App\MeetEntryPayment;
use App\MeetEntryStatus;
use App\MeetEntryStatusCode;
use App\PayPalPayment;

use Exception;
use Log;
use Illuminate\Http\Request;

use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\PaymentExecution;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

class PaypalController extends Controller {
    private $request;
    private $apiContext;

    public function __construct(Request $request) {
        $this->request = $request;

        $this->apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                env('PAYPAL_CLIENT_ID'),     // ClientID
                env('PAYPAL_SECRET_KEY')      // ClientSecret
            )
        );

        $this->apiContext->setConfig(array('mode' => env('PAYPAL_MODE', 'sandbox'),
            'log.LogEnabled' => true,
            'log.FileName' => '../PayPal.log',
            'log.LogLevel' => env('PAYPAL_LOGLEVEL', 'INFO'), // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
            'cache.enabled' => true));

//        $this->logger = new \Monolog\Logger('paypal');
//        $this->logger->pushProcessor(new \Monolog\Processor\WebProcessor);
//        $this->logger->pushHandler(new \Monolog\Handler\StreamHandler($GLOBALS['log_dir'] . 'paypal.log', $GLOBALS['log_level']));

    }

    public function createPaymentEntryById($id) {

        $meetEntry = MeetEntry::find($id);
        $entryId = $meetEntry->id;
        $meetId = $meetEntry->meet_id;
        $meet = Meet::find($meetId);
        $meetName = $meet->meetname;
        $meetCost = $meet->meetfee;

        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        $itemList = new ItemList();
        $arrItems = array();

//        foreach ($this->items as $i) {
//
            $objItem = new Item();
            $objItem->setName('Meet Fee');
            $objItem->setQuantity(1);
            $objItem->setPrice($meetCost);
            $objItem->setCurrency("AUD");

            $arrItems[] = $objItem;
//
//        }

        $itemList->setItems($arrItems);

        $amount = new Amount();
        $amount->setCurrency("AUD")
            ->setTotal($meetCost);

        $transaction = new Transaction();
        $invoiceId = uniqid();

        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription($meetName . " - Entry " . $entryId)
            ->setInvoiceNumber($invoiceId);

        //$baseUrl = "http://localhost:8888";
        $redirectUrls = new RedirectUrls();
//        $redirectUrls->setReturnUrl(env('SITE_BASE', '') . "/enter/" . $meetId . "/confirmation?paypalsuccess=true")
//                ->setCancelUrl(env('SITE_BASE', '') . "/enter/" . $meetId . "/confirmation?paypalsuccess=false");
        $redirectUrls->setReturnUrl(env('SITE_BASE', '') . "/paypal-landing?paypalsuccess=true&meet_entry=" . $entryId)
            ->setCancelUrl(env('SITE_BASE', '') . "/paypal-landing/paypalsuccess=false&meet_entry=" . $entryId);


        $payment = new Payment();
        $payment->setIntent("sale")
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions(array($transaction));

        $request = clone $payment;

        try {

            $payment->create($this->apiContext);

            $approvalUrl = $payment->getApprovalLink();

            // Store the payment details
            // $this->storePayment();
            $paymentInfo = array("meet_entry_id" => $entryId,
                "invoice_id" => $invoiceId);

            $paypalPayment = PayPalPayment::create($paymentInfo);


        } catch (Exception $ex) {

//            $this->logger->error("Payment Creation Exception: " . $ex);
            $approvalUrl = 'error';

            Log::error($ex);

            return response()->json([
                'exception' => $ex->getMessage()], 500);

        }

        return response()->json([
            'payment' => $paypalPayment,
            'approvalUrl' => $approvalUrl], 200);


    }

    public function createPaymentIncompleteEntryById($id) {
        $entry = MeetEntryIncomplete::find($id);
        $entryId = $id;
        $meetId = $entry->meet_id;
        $meet = Meet::find($meetId);
        $meetName = $meet->meetname;
        $meetCost = $meet->meetfee;

        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        $itemList = new ItemList();
        $arrItems = array();

//        foreach ($this->items as $i) {
//
        $objItem = new Item();
        $objItem->setName('Meet Fee');
        $objItem->setQuantity(1);
        $objItem->setPrice($meetCost);
        $objItem->setCurrency("AUD");

        $arrItems[] = $objItem;
//
//        }

        $itemList->setItems($arrItems);

        $amount = new Amount();
        $amount->setCurrency("AUD")
            ->setTotal($meetCost);

        $transaction = new Transaction();
        $invoiceId = uniqid();

        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription($meetName . " - Entry Pending")
            ->setInvoiceNumber($invoiceId);

        //$baseUrl = "http://localhost:8888";
        $redirectUrls = new RedirectUrls();
//        $redirectUrls->setReturnUrl(env('SITE_BASE', '') . "/enter/" . $meetId . "/confirmation?paypalsuccess=true")
//            ->setCancelUrl(env('SITE_BASE', '') . "/enter/" . $meetId . "/confirmation?paypalsuccess=false");

        $redirectUrls->setReturnUrl(env('SITE_BASE', '') . "/paypal-landing?paypalsuccess=true&pending_entry=" . $entryId)
            ->setCancelUrl(env('SITE_BASE', '') . "/paypal-landing?paypalsuccess=false&pending_entry=" . $entryId);

        $payment = new Payment();
        $payment->setIntent("sale")
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions(array($transaction));

        $request = clone $payment;

        try {

            $payment->create($this->apiContext);

            $approvalUrl = $payment->getApprovalLink();

            // Store the payment details
            // $this->storePayment();
            $paymentInfo = array("meet_entries_incomplete_id" => $entryId,
                "invoice_id" => $invoiceId);

            $paypalPayment = PayPalPayment::create($paymentInfo);

        } catch (Exception $ex) {

//            $this->logger->error("Payment Creation Exception: " . $ex);
            $approvalUrl = 'error';

            Log::error($ex);

            return response()->json([
                'exception' => $ex->getMessage()], 500);

        }

        return response()->json([
            'payment' => $paypalPayment,
            'approvalUrl' => $approvalUrl], 200);

    }

    public function finalisePayment() {

        $paymentData = $this->request->all();

        $paymentId = $paymentData['paymentId'];
        $payerID = $paymentData['payerID'];
        Log::debug($paymentData);

        try {

            $payment = Payment::get($paymentId, $this->apiContext);
            $execution = new PaymentExecution();

            $execution->setPayerId($payerID);

            $paidAmount = 0;

            $result = $payment->execute($execution, $this->apiContext);

            $transactions = $result->getTransactions();

            $paidAmount = $transactions[0]->getAmount()->getTotal();

//            debug("PayPal payment info: " . $result);
            // Retreive the invoice id
            $invoiceId = $transactions[0]->getInvoiceNumber();

        } catch (Exception $ex) {

            Log::error($ex);

            return response()->json([
                'exception' => $ex->getMessage()], 500);

        }


        // Get the entry Id associated with this one
        $paypalPayment = PayPalPayment::where('invoice_id', '=', $invoiceId)->first();

        if ($paypalPayment == NULL) {
            Log::error('Unable to find invoice_id in paypal payment table');

            return response()->json([
                'paidAmount' => $paidAmount,
                'invoiceId' => $invoiceId], 200);

        }

        $paypalPayment = $this->updatePaypalPayment($paypalPayment, $payment, $paidAmount);

        $entryId = $paypalPayment->meet_entry_id;
        $incompleteId = $paypalPayment->meet_entries_incomplete_id;

        if ($entryId != NULL) {

            // Load the entry and record payment
            $entry = MeetEntry::find($entryId);

            $paymentMethodId = 1;

            $meetEntryPaymentDetails = array("entry_id" => $entryId,
                "member_id" => $entry->member_id,
                "received" => date('Y-m-d H:i:s'),
                "amount" => $paidAmount,
                "method" => $paymentMethodId,
                "comment" => "PayPal Invoice " . $invoiceId);
            $meetEntryPayment = MeetEntryPayment::create($meetEntryPaymentDetails);

            // Update payment status
            $acceptedStatus = MeetEntryStatusCode::where('label', '=', 'Accepted')->first();
            if ($entryId != null) {
                $meetEntryStatus = new MeetEntryStatus();
                $meetEntryStatus->entry_id = $entryId;
                $meetEntryStatus->code = $acceptedStatus->id;
                $meetEntryStatus->saveOrFail();
            }

            return response()->json([
                'paypalPayment' => $paypalPayment,
                'meetEntryPayment' => $meetEntryPayment,
                'status' => $acceptedStatus->id,
                'paid' => $paidAmount], 200);

        }

        if ($incompleteId != NULL) {

            $incompleteEntry = MeetEntryIncomplete::find($incompleteId);
            $paymentMethodId = 1;

            $acceptedStatus = MeetEntryStatusCode::where('label', '=', 'Paid Pending')->first();
            $incompleteEntry->status_id = $acceptedStatus->id;
            $incompleteData = json_decode($incompleteEntry->entrydata, true);

            $incompleteEntryPaymentDetails = array(
                "received" => date('Y-m-d H:i:s'),
                "amount" => $paidAmount,
                "method" => $paymentMethodId,
                "comment" => "PayPal Invoice " . $invoiceId);

            $incompleteData['paymentDetails'] = $incompleteEntryPaymentDetails;
            $incompleteEntry->entrydata = json_encode($incompleteData);
            $incompleteEntry->saveOrFail();

            return response()->json([
                'paypalPayment' => $paypalPayment,
                'incompleteEntry' => $incompleteEntry,
                'status' => $acceptedStatus->id,
                'paid' => $paidAmount], 200);
        }

        return response()->json([
            'paypalPayment' => $paypalPayment,
            'paid' => $paidAmount,
            'message' => 'Unable to update entry'], 200);

    }

    private function updatePaypalPayment($paypalPayment, $payment, $paidAmount) {
        // Retrieve the payer details
        $payer = $payment->getPayer();
        $payerInfo = $payer->getPayerInfo();
        $payerName = $payerInfo->getFirstName() . ' ' . $payerInfo->getLastName();
        $payerEmail = $payerInfo->getEmail();

        // Log the details
//            $this->logger->info("finalisePayment: $paidAmount for entry " . $this->entryId .
//                " for entrant " . $this->payerName . " <" . $this->payerEmail . ">");

        // Update table
        $paypalPayment->paid = $paidAmount;
        $paypalPayment->payer_name = $payerName;
        $paypalPayment->payer_email = $payerEmail;
        $paypalPayment->saveOrFail();

        return $paypalPayment;
    }

}