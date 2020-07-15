<?php

namespace App\Http\Controllers;

use PayPal\Api\Item;
use PayPal\Api\Payer;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Payment;
use PayPal\Api\ItemList;
use PayPal\Api\Transaction;
use PayPal\Rest\ApiContext;
use Illuminate\Http\Request;
use PayPal\Api\RedirectUrls;
use PayPal\Api\PaymentExecution;
use App\Http\Controllers\Controller;
use PayPal\Auth\OAuthTokenCredential;
use Illuminate\Support\Facades\Config;

class PaypalPaymentController extends Controller
{
    public $apiContext;
    public function __construct() {
        $this->apiContext = new ApiContext(
            new OAuthTokenCredential(
                'AagTVUgNZT572JKlTaVGrk6pJ3tEditMKTnV1wMyMRlfXFl87ctFY4IfMnRAXb2lqKXKoyn2kv63YPUU',     // ClientID
                'EB82shxpqXUne_tLx--PGJZRzcUEG2fe9YfQS6H1jnBsGwZuE-__K1sCrLQLc9eCNteiNVQOwBtqq525'      // ClientSecret
            )
        );
    }

    public function create()
    {
        $payer = new Payer();
        $payer->setPaymentMethod("paypal");
        
        $item1 = new Item();
        $item1->setName('Ground Coffee 40 oz')
            ->setCurrency('USD')
            ->setQuantity(1)
            ->setSku("123123") // Similar to `item_number` in Classic API
            ->setPrice(7.5);

        $item2 = new Item();
        $item2->setName('Granola bars')
            ->setCurrency('USD')
            ->setQuantity(5)
            ->setSku("321321") // Similar to `item_number` in Classic API
            ->setPrice(2);

        $itemList = new ItemList();
        $itemList->setItems(array($item1, $item2));

        $details = new Details();
        $details->setShipping(1.2)
                ->setTax(1.3)
                ->setSubtotal(17.50);

        $amount = new Amount();
        $amount->setCurrency("USD")
               ->setTotal(20)
               ->setDetails($details);
    
        $transaction = new Transaction();
        $transaction->setAmount($amount)
                    ->setItemList($itemList)
                    ->setDescription("Payment description")
                    ->setInvoiceNumber(uniqid());
        
        $baseUrl = Config::get('app.url');
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl("$baseUrl/execute-paypal-payment")
                     ->setCancelUrl("$baseUrl/cancel-paypal-payment");

        $payment = new Payment();
        $payment->setIntent("sale")
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions(array($transaction));

        $request = clone $payment;

        $payment->create($this->apiContext);

        return redirect($approvalUrl = $payment->getApprovalLink());
    }
    
    public function execute()
    {
        $paymentId = $_GET['paymentId'];
        $payment = Payment::get($paymentId, $this->apiContext);

        $execution = new PaymentExecution();
        $execution->setPayerId($_GET['PayerID']);

        $transaction = new Transaction();
        $amount = new Amount();
        $details = new Details();

        $details->setShipping(1.2)
                ->setTax(1.3)
                ->setSubtotal(17.50);

        $amount->setCurrency('USD');
        $amount->setTotal(20);
        $amount->setDetails($details);
        $transaction->setAmount($amount);

        $execution->addTransaction($transaction);
        
        return $result = $payment->execute($execution, $this->apiContext);
    }
}
