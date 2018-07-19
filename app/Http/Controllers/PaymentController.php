<?php

namespace App\Http\Controllers;

use App\Invoice;
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
use PayPal\Api\ExecutePayment;
use PayPal\Api\PaymentExecution;
use PayPal\Auth\OAuthTokenCredential;
use App\Repositories\InvoiceRepository;
use App\Repositories\PaymentRepository;
use App\Http\Requests\PaypalPaymentRequest;
use App\Http\Requests\StripePaymentRequest;
use App\Repositories\TransactionRepository;

class PaymentController extends Controller
{
    protected $request;
    protected $repo;
    protected $invoice;
    protected $transaction;

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(
        Request $request,
        PaymentRepository $repo,
        InvoiceRepository $invoice,
        TransactionRepository $transaction
    ) {
        $this->request = $request;
        $this->repo    = $repo;
        $this->invoice = $invoice;

        $paypal_conf = config('paypal');
        $this->_api_context = new ApiContext(new OAuthTokenCredential($paypal_conf['client_id'], $paypal_conf['secret']));
        $this->_api_context->setConfig($paypal_conf['settings']);
        $this->transaction = $transaction;
    }

    /**
     * Used to validate coupon
     * @post ("/api/payment/validate/coupon")
     * @param ({
     *      @Parameter("coupon", type="string", required="optional", description="Coupon to validate"),
     *      @Parameter("amount", type="numeric", required="true", description="Amount")
     * })
     * @return Response
     */
    public function validateCoupon()
    {
        $coupon_response = $this->repo->validateCoupon($this->request->all());

        $coupon_discount = isset($coupon_response['discount']) ? $coupon_response['discount'] : 0;

        $discount = ($coupon_discount) ? (request('amount') * ($coupon_discount/100)) : 0;

        $message = isset($coupon_response['message']) ? $coupon_response['message'] : '';

        return $this->success(compact('message', 'discount'));
    }

    /**
     * Process credit card payment
     * @post ("/api/payment/{invoice_uuid}/credit-card")
     * @param ({
     *      @Parameter("uuid", type="string", required="required", description="Unique Id of Invoice")
     * })
     * @return Response
     */
    public function creditCard(StripePaymentRequest $request, $invoice_uuid)
    {
        $this->authorize('makePayment', Invoice::class);

        $invoice = $this->invoice->findByUuidOrFail($invoice_uuid);

        $this->invoice->isCancelled($invoice);

        $this->invoice->isDraft($invoice);

        $this->invoice->accessible($invoice);

        $transaction = $this->repo->processCreditCard($invoice, $this->request->all());

        return $this->success(['message' => trans('payment.payment_made')]);
    }

    /**
     * Process paypal payment
     * @post ("/api/payment/{invoice_uuid}/paypal")
     * @param ({
     *      @Parameter("uuid", type="string", required="required", description="Unique Id of Invoice")
     * })
     * @return Response
     */
    public function paypal(PaypalPaymentRequest $request, $invoice_uuid)
    {
        $this->authorize('makePayment', Invoice::class);

        $invoice = $this->invoice->findByUuidOrFail($invoice_uuid);

        $this->invoice->isCancelled($invoice);

        $this->invoice->isDraft($invoice);

        $this->invoice->accessible($invoice);

        $transaction = $this->repo->processPaypal($invoice, $this->request->all());

        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        $amount = $transaction->amount;
        $currency = $invoice->Currency->name;
        $invoice_detail = $invoice->invoice_number;

        $item_1 = new Item();
        $item_1->setName($invoice_detail)
        ->setCurrency($currency)
        ->setQuantity(1)
        ->setPrice($amount);

        $total = $amount;

        $item_list = new ItemList();
        $item_list->setItems(array($item_1));

        $amount = new Amount();
        $amount->setCurrency($currency)
        ->setTotal($total);

        $paypal_transaction = new Transaction();
        $paypal_transaction->setAmount($amount)
        ->setItemList($item_list)
        ->setDescription($invoice_detail);

        $redirect_urls = new RedirectUrls();
        $redirect_urls->setReturnUrl(url('paypal/status'))
        ->setCancelUrl(url('paypal/status'));

        $payment = new Payment();
        $payment->setIntent('Sale')
        ->setPayer($payer)
        ->setRedirectUrls($redirect_urls)
        ->setTransactions(array($paypal_transaction));

        try {
            $payment->create($this->_api_context);
        } catch (\PayPal\Exception\PPConnectionException $ex) {
            if (\config('app.debug')) {
                echo "Exception: " . $ex->getMessage() . PHP_EOL;
                $err_data = json_decode($ex->getData(), true);
                exit;
            } else {
                return redirect('/paypal/status')->withErrors(trans('general.something_wrong'));
            }
        }

        foreach ($payment->getLinks() as $link) {
            if ($link->getRel() === 'approval_url') {
                $redirect_url = $link->getHref();
                break;
            }
        }

        $transaction->token = $payment->getId();
        $transaction->save();

        \Cache::put('paypal_payment_id', $payment->getId(), 60);

        $redirect_url = isset($redirect_url) ? $redirect_url : '/paypal/status';

        return $redirect_url;
    }

    /**
     * Fetch paypal payment status
     * @post ("/paypal/status")
     * @param ({
     *      @Parameter("PayerID", type="string", required="required", description="PayerID from Paypal")
     *      @Parameter("token", type="string", required="required", description="Token from Paypal")
     * })
     * @return Response
     */
    public function paypalStatus()
    {
        $payment_id = \Cache::get('paypal_payment_id');

        \Cache::forget('paypal_payment_id');

        if (empty(request('PayerID')) || empty(request('token'))) {
            return redirect('/')->withErrors(trans('payment.failed'));
        }

        $payment = Payment::get($payment_id, $this->_api_context);

        $execution = new PaymentExecution();
        $execution->setPayerId(request('PayerID'));

        $result = $payment->execute($execution, $this->_api_context);
        $transaction = $this->transaction->findByTokenOrFail($payment_id);

        if ($result->getState() != 'approved') {
            $this->repo->failedPayment($transaction, '');
        } else {
            $transaction = $this->repo->completePaypal($transaction);
        }

        return redirect('/invoice/'.$transaction->Invoice->uuid.'/payment/'.$transaction->uuid.'/response');
    }
}
