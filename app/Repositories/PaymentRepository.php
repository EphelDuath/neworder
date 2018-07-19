<?php
namespace App\Repositories;

use App\Invoice;
use Stripe\Error\Api;
use Stripe\Error\Card;
use Illuminate\Support\Str;
use Stripe\Error\RateLimit;
use App\Jobs\SendInvoiceMail;
use Stripe\Error\ApiConnection;
use Stripe\Error\Authentication;
use Stripe\Error\InvalidRequest;
use App\Repositories\CouponRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\ActivityLogRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Validation\ValidationException;

class PaymentRepository
{
	protected $invoice;
	protected $transaction;
	protected $coupon;
	protected $activity;

    /**
     * Instantiate a new instance.
     *
     * @return void
     */
	public function __construct(
		InvoiceRepository $invoice,
		TransactionRepository $transaction,
		CouponRepository $coupon,
		ActivityLogRepository $activity
	)
	{
        $this->invoice     = $invoice;
        $this->transaction = $transaction;
        $this->coupon      = $coupon;
        $this->activity    = $activity;
	}

    /**
     * Validate payment.
     *
     * @param Invoice $invoice
     * @param array $params
     * @return void
     */
	public function validate(Invoice $invoice, $params = array())
	{
        $amount = isset($params['amount']) ? $params['amount'] : '';
        $paid = $this->transaction->getInvoicePaidAmount($invoice->id);
        $balance = $invoice->total - $paid;

        if ($amount > $balance) {
            throw ValidationException::withMessages(['message' => trans('invoice.balance_less_than_payment_amount')]);
        }

        if (date('Y-m-d') < $invoice->date) {
            throw ValidationException::withMessages(['message' => trans('invoice.payment_date_less_than_invoice_date')]);
        }

        if (!$invoice->partial_payment && $amount != $invoice->total) {
            throw ValidationException::withMessages(['message' => trans('invoice.partial_payment_disabled')]);
        }
	}

    /**
     * Validate payment.
     *
     * @param Invoice $invoice
     * @param array $params
     * @return void
     */
	public function validateCoupon($params = array())
	{
		$coupon = isset($params['coupon']) ? $params['coupon'] : '';
		$amount = isset($params['amount']) ? $params['amount'] : '';

		if (! $coupon)
			return [];

		if (! is_numeric($amount) || $amount < 0) {
			throw ValidationException::withMessages(['message' => trans('validation.numeric',['attribute' => trans('payment.amount')])]);
		}

        return $this->coupon->validate($coupon);
	}

    /**
     * Create transaction.
     *
     * @param Invoice $invoice
     * @param array $params
     * @return Transaction
     */
	private function createTransaction(Invoice $invoice, $params = array())
	{
		return $this->transaction->createCustomerTransaction([
            'uuid'             => Str::uuid(),
            'customer_id'      => \Auth::user()->id,
            'currency_id'      => $invoice->currency_id,
            'conversion_rate'  => 1,
            'amount'           => 0,
            'date'             => date('Y-m-d'),
            'reference_number' => $invoice->reference_number,
            'invoice_id'       => $invoice->id,
            'address_line_1'   => isset($params['address_line_1']) ? $params['address_line_1'] : null,
            'address_line_2'   => isset($params['address_line_2']) ? $params['address_line_2'] : null,
            'city'             => isset($params['city']) ? $params['city'] : null,
            'state'            => isset($params['state']) ? $params['state'] : null,
            'zipcode'          => isset($params['zipcode']) ? $params['zipcode'] : null,
            'country'          => isset($params['country']) ? $params['country'] : null,
            'phone'            => isset($params['phone']) ? $params['phone'] : null,
        ]);
	}

    /**
     * Process failed payment.
     *
     * @param Transaction $transaction
     * @param string $gateway_response
     * @return void
     */
	private function failedPayment($transaction, $gateway_response)
	{
        $transaction->status = 0;
        $transaction->gateway_response = $gateway_response;
        $transaction->gateway_status = 'error';
        $transaction->save();

        $this->activity->record([
            'module'     => 'invoice',
            'module_id'  => $transaction->invoice_id,
            'sub_module' => $transaction->Invoice->invoice_number,
            'activity'   => 'payment_failed'
        ]);

        SendInvoiceMail::dispatch(\Auth::user()->email,['slug' => 'invoice-payment-failure'], $transaction->Invoice ,$transaction);

        if ($gateway_response) {
        	throw ValidationException::withMessages(['message' => $gateway_response]);
        }
	}

    /**
     * Process transaction.
     *
     * @param Invoice $invoice
     * @param string $gateway
     * @param array $params
     * @return Transaction
     */
	private function processTransaction(Invoice $invoice, $gateway, $params = array())
	{
        $this->validate($invoice, $params);

		$coupon = isset($params['coupon']) ? $params['coupon'] : '';
		$input_amount = isset($params['amount']) ? $params['amount'] : 0;

        $coupon_response = $this->coupon->validate($coupon);

        $coupon_discount = isset($coupon_response['discount']) ? $coupon_response['discount'] : 0;

        $discount = $coupon_discount ? ($input_amount * ($coupon_discount/100)) : 0;
        $amount = currency(($input_amount - $discount), $invoice->Currency);
        $discount = currency(($input_amount - $amount),$invoice->Currency);

        $transaction = $this->createTransaction($invoice, $params);

        $transaction->coupon = ($discount) ? $coupon : null;
        $transaction->amount = $amount;
        $transaction->source = $gateway;
        $transaction->coupon_discount = $discount;
        $transaction->save();

        return $transaction;
	}

    /**
     * Process credit card payment.
     *
     * @param Invoice $invoice
     * @param array $params
     * @return Transaction
     */
	public function processCreditCard(Invoice $invoice, $params = array())
	{
		$transaction = $this->processTransaction($invoice, 'stripe', $params);
		$stripeToken = isset($params['stripeToken']) ? $params['stripeToken'] : null;

        \Stripe\Stripe::setApiKey(config('config.stripe_private_key'));
        try {
            $charge = \Stripe\Charge::create([
                'amount'   => $transaction->amount * 100,
                'currency' => $invoice->Currency->name,
                'source'   => $stripeToken
                ]);
        } catch (Card $e) {
        	$this->failedPayment($transaction, $e->getMessage());
        }
        catch (Api $e) {
        	$this->failedPayment($transaction, $e->getMessage());
        }
        catch (InvalidRequest $e) {
        	$this->failedPayment($transaction, $e->getMessage());
        }
        catch (RateLimit $e) {
        	$this->failedPayment($transaction, $e->getMessage());
        }
        catch (ApiConnection $e) {
        	$this->failedPayment($transaction, $e->getMessage());
        }
        catch (Authentication $e) {
        	$this->failedPayment($transaction, $e->getMessage());
        }

        $transaction->status = 1;
        $transaction->token = strtoupper(randomString(25));
        $transaction->gateway_status = 'success';
        $transaction->save();

        if($transaction->coupon)
            $this->coupon->incrementUseCount($transaction->coupon);

        $this->invoice->updateStatus($invoice);

        $this->activity->record([
            'module'     => 'invoice',
            'module_id'  => $invoice->id,
            'sub_module' => $invoice->invoice_number,
            'activity'   => 'paid'
        ]);

        SendInvoiceMail::dispatch($invoice->Customer->email,['slug' => 'invoice-payment-success'], $invoice ,$transaction);

        return $transaction;
	}

    /**
     * Process paypal payment.
     *
     * @param Invoice $invoice
     * @param array $params
     * @return Transaction
     */
	public function processPaypal(Invoice $invoice, $params = array())
	{
		$transaction = $this->processTransaction($invoice, 'paypal', $params);

        return $transaction;
	}

    /**
     * Complete paypal transaction.
     *
     * @param Transaction $transaction
     * @return Transaction
     */
	public function completePaypal($transaction)
	{
        $transaction->status = 1;
        $transaction->gateway_status = 'success';
        $transaction->save();

        $invoice = $transaction->Invoice;

        if($transaction->coupon)
            $this->coupon->incrementUseCount($transaction->coupon);

        $this->invoice->updateStatus($invoice);

        $this->activity->record([
            'user_id'    => $invoice->customer_id,
            'module'     => 'invoice',
            'module_id'  => $invoice->id,
            'sub_module' => $invoice->invoice_number,
            'activity'   => 'paid'
        ]);

        SendInvoiceMail::dispatch($invoice->Customer->email,['slug' => 'invoice-payment-success'], $invoice ,$transaction);

        return $transaction;
	}
}