<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [];
    protected $primaryKey = 'id';
    protected $table = 'transactions';

    public function account()
    {
        return $this->belongsTo('App\Account');
    }

    public function fromAccount()
    {
        return $this->belongsTo('App\Account', 'from_account_id');
    }

    public function customer()
    {
        return $this->belongsTo('App\User', 'customer_id');
    }

    public function supplier()
    {
        return $this->belongsTo('App\Supplier', 'supplier_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo('App\PaymentMethod');
    }

    public function currency()
    {
        return $this->belongsTo('App\Currency');
    }

    public function incomeCategory()
    {
        return $this->belongsTo('App\IncomeCategory');
    }

    public function expenseCategory()
    {
        return $this->belongsTo('App\ExpenseCategory');
    }

    public function invoice()
    {
        return $this->belongsTo('App\Invoice');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function scopeFilterById($q, $id)
    {
        if (! $id) {
            return $q;
        }

        return $q->where('id', '=', $id);
    }

    public function scopeFilterByUuid($q, $uuid)
    {
        if (! $uuid) {
            return $q;
        }

        return $q->where('uuid', '=', $uuid);
    }

    public function scopeFilterByToken($q, $token)
    {
        if (! $token) {
            return $q;
        }

        return $q->where('token', '=', $token);
    }

    public function scopeFilterByUserId($q, $user_id)
    {
        if (! $user_id) {
            return $q;
        }

        return $q->where('user_id', '=', $user_id);
    }

    public function scopeFilterByStatus($q, $status)
    {
        return $q->where('status', '=', $status);
    }

    public function scopeFilterByGatewayStatus($q, $gateway_status)
    {
        if (! $gateway_status) {
            return $q;
        }

        return $q->where('gateway_status', '=', $gateway_status);
    }

    public function scopeFilterByHead($q, $head)
    {
        if (! $head) {
            return $q;
        }

        return $q->where('head', '=', $head);
    }

    public function scopeFilterByExpenseCategoryId($q, $expense_category_id)
    {
        if (! $expense_category_id) {
            return $q;
        }

        return $q->where('expense_category_id', '=', $expense_category_id);
    }

    public function scopeFilterByIncomeCategoryId($q, $income_category_id)
    {
        if (! $income_category_id) {
            return $q;
        }

        return $q->where('income_category_id', '=', $income_category_id);
    }

    public function scopeFilterByPaymentMethodId($q, $payment_method_id)
    {
        if (! $payment_method_id) {
            return $q;
        }

        return $q->where('payment_method_id', '=', $payment_method_id);
    }

    public function scopeFilterByAccountId($q, $account_id)
    {
        if (! $account_id) {
            return $q;
        }

        return $q->where('account_id', '=', $account_id);
    }

    public function scopeFilterByFromAccountId($q, $from_account_id)
    {
        if (! $from_account_id) {
            return $q;
        }

        return $q->where('from_account_id', '=', $from_account_id);
    }

    public function scopeFilterByCustomerId($q, $customer_id)
    {
        if (! $customer_id) {
            return $q;
        }

        return $q->where('customer_id', '=', $customer_id);
    }

    public function scopeFilterBySupplierId($q, $supplier_id)
    {
        if (! $supplier_id) {
            return $q;
        }

        return $q->where('supplier_id', '=', $supplier_id);
    }

    public function scopeFilterByCurrencyId($q, $currency)
    {
        if (! $currency) {
            return $q;
        }

        return $q->where('currency', '=', $currency);
    }

    public function scopeFilterByInvoiceId($q, $invoice_id)
    {
        if (! $invoice_id) {
            return $q;
        }

        return $q->where('invoice_id', '=', $invoice_id);
    }

    public function scopeFilterByReferenceNumber($q, $reference_number)
    {
        if (! $reference_number) {
            return $q;
        }

        return $q->where('reference_number', '=', $reference_number);
    }

    public function scopeDateBetween($q, $dates)
    {
        if ((! $dates['start_date'] || ! $dates['end_date']) && $dates['start_date'] <= $dates['end_date']) {
            return $q;
        }

        return $q->where('date', '>=', getStartOfDate($dates['start_date']))->where('date', '<=', getEndOfDate($dates['end_date']));
    }
}
