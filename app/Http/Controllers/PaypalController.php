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
use App\MeetMerchandise;
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

        // Check if entry is already fully paid
        $existingPayments = $meetEntry->payments;
        $alreadyPaid = 0;
        if (isset($existingPayments)) {
            foreach ($existingPayments as $p) {
                $alreadyPaid += $p->amount;
            }
        }

        if ($alreadyPaid >= $meetEntry->cost) {
            Log::error('Attempt to pay for entry that is already fully paid. Entry Id: ' . $id . ', Entry Cost:'
                . $meetEntry->cost . ', Already Paid: ' . $alreadyPaid);
            return response()->json([
                'success' => false,
                'paid' => $alreadyPaid,
                'message' => 'Your entry is already fully paid'], 200);

        }

        $entryId = $meetEntry->id;
        $entryCode = $meetEntry->code;
        $meetId = $meetEntry->meet_id;
        $meet = Meet::find($meetId);
        $meetName = $meet->meetname;

        // Get entry cost
        // TODO: itemisation
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
            $objItem->setPrice($meetEntry->cost);
            $objItem->setCurrency("AUD");

            $arrItems[] = $objItem;
//
//        }

//        if ($meetEntry->meals !== NULL && intval($meetEntry->meals) !== 0) {
//            $mealItem = new Item();
//            $mealItem->setName('Meal Fees');
//            $mealItem->setQuantity(intval($meetEntry->meals));
//            $mealPrice = floatval($meet->mealfee) * intval($meetEntry->meals);
//            $mealItem->setPrice($mealPrice);
//            $mealItem->setCurrency("AUD");
//
//            $arrItems[] = $mealItem;
//
//        }

        $itemList->setItems($arrItems);

        $amount = new Amount();
        $amount->setCurrency("AUD")
            ->setTotal($meetEntry->cost);

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
        $redirectUrls->setReturnUrl(env('SITE_BASE', '') . "/paypal-landing?paypalsuccess=true&meet_entry=" . $entryCode)
            ->setCancelUrl(env('SITE_BASE', '') . "/paypal-landing/paypalsuccess=false&meet_entry=" . $entryCode);


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

            Log::debug('Attempting to create PayPalPayment object for entry: ' . $entryId .
                ', invoice id: ' . $invoiceId);

            $paypalPayment = PayPalPayment::create($paymentInfo);

            Log::info('PayPalPayment successfully created for entry: ' . $entryId . ', invoice id: ' . $invoiceId);

        } catch (Exception $ex) {

//            $this->logger->error("Payment Creation Exception: " . $ex);
            $approvalUrl = 'error';

            // Log::error('Exception while trying to create payment', $ex);

            \Sentry\captureException($ex);
            \Sentry\captureMessage($ex->getData());

            return response()->json([
                'success' => false,
                'exception' => $ex->getMessage(),
                'details' => $ex->getData()], 500);

        }

        return response()->json([
            'success' => true,
            'payment' => $paypalPayment,
            'approvalUrl' => $approvalUrl], 200);


    }

    public function createPaymentIncompleteEntryById($id) {
        $entry = MeetEntryIncomplete::find($id);
        $entryId = $id;
        $meetId = $entry->meet_id;
        $entryData = json_decode($entry->entrydata);
        $pendingCode = $entry->code;
        $meet = Meet::find($meetId);
        $meetName = $meet->meetname;

        if ($entryData->membershipDetails->member_type === 'msa'
            || $entryData->membershipDetails->member_type === 'international') {
            $meetCost = $meet->meetfee;
        } else {
            if ($meet->meetfee_non_member !== NULL) {
                $meetCost = $meet->meetfee_non_member;
            } else {
                $meetCost = $meet->meetfee;
            }
        }

        $eventCost = 0;
        $mealCost = 0;
        $merchandiseCost = 0;
        $numIndividualEvents = 0;

        foreach ($entryData->entryEvents as $eventEntry) {
            foreach ($meet->events as $e) {
                if ($e->id == $eventEntry->event_id) {
                    if ($e->legs === 1) {
                        $numIndividualEvents++;
                        if ($entryData->membershipDetails->member_type === 'msa'
                            || $entryData->membershipDetails->member_type === 'international') {
                            $eventCost += $e->eventfee;
                        } else {
                            $eventCost += $e->eventfee_non_member;
                        }

                        if ($numIndividualEvents > $meet->included_events) {
                            if ($meet->included_events !== NULL && $meet->extra_event_fee !== NULL) {
                                $eventCost += $meet->extra_event_fee;
                            }
                        }
                    }
                }



            }
        }

        if (isset($entryData->mealMerchandiseDetails)) {
            $mealCost += $entryData->mealMerchandiseDetails->meals * $meet->mealfee;

            if (isset($entryData->mealMerchandiseDetails->merchandiseItems)) {
                foreach ($entryData->mealMerchandiseDetails->merchandiseItems as $m) {
                    $merchandiseDetails = MeetMerchandise::find($m->merchandiseId);

                    $itemCost = 0;

                    if ($merchandiseDetails !== NULL) {
                        $itemCost = $merchandiseDetails->total_price * $m->qty;
                    }

                    $merchandiseCost += $itemCost;
                }
            }
        }

        $entryCost = $meetCost + $eventCost + $mealCost + $merchandiseCost;

        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        $itemList = new ItemList();
        $arrItems = array();

//        foreach ($this->items as $i) {
//
        $objItem = new Item();
        $objItem->setName('Meet Fee');
        $objItem->setQuantity(1);
        $objItem->setPrice($entryCost);
        $objItem->setCurrency("AUD");

        $arrItems[] = $objItem;
//
//        }

        $itemList->setItems($arrItems);

        $amount = new Amount();
        $amount->setCurrency("AUD")
            ->setTotal($entryCost);

        $transaction = new Transaction();
        $invoiceId = uniqid();

        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription($meetName . " - Entry Pending " . $entryId)
            ->setInvoiceNumber($invoiceId);

        //$baseUrl = "http://localhost:8888";
        $redirectUrls = new RedirectUrls();
//        $redirectUrls->setReturnUrl(env('SITE_BASE', '') . "/enter/" . $meetId . "/confirmation?paypalsuccess=true")
//            ->setCancelUrl(env('SITE_BASE', '') . "/enter/" . $meetId . "/confirmation?paypalsuccess=false");

        $redirectUrls->setReturnUrl(env('SITE_BASE', '') . "/paypal-landing?paypalsuccess=true&pending_entry=" . $pendingCode)
            ->setCancelUrl(env('SITE_BASE', '') . "/paypal-landing?paypalsuccess=false&pending_entry=" . $pendingCode);

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

        if ($paypalPayment == null) {
            return response()->json([
                'success' => false,
                'paypalPayment' => $paypalPayment,
                'paid' => $paidAmount,
                'message' => 'PayPal Payment already finalised'], 200);
        }

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
                'status_label' => $acceptedStatus->label,
                'status_description' => $acceptedStatus->description,
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
                'status_label' => $acceptedStatus->label,
                'status_description' => $acceptedStatus->description,
                'paid' => $paidAmount], 200);
        }

        return response()->json([
            'success' => false,
            'paypalPayment' => $paypalPayment,
            'paid' => $paidAmount,
            'message' => 'Unable to update entry'], 400);

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
        if ($paypalPayment->paid != NULL) {
            // Already finalised
            return null;
        }

        $paypalPayment->paid = $paidAmount;
        $paypalPayment->payer_name = $payerName;
        $paypalPayment->payer_email = $payerEmail;
        // TODO: link meet entry payment

        $paypalPayment->saveOrFail();

        return $paypalPayment;
    }

}