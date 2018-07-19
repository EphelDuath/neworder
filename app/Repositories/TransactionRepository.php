<?php
namespace App\Repositories;

use App\Transaction;
use Illuminate\Support\Str;
use App\Repositories\UserRepository;
use App\Repositories\UploadRepository;
use App\Repositories\SupplierRepository;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\IncomeCategoryRepository;
use Illuminate\Validation\ValidationException;
use App\Repositories\ExpenseCategoryRepository;

class TransactionRepository
{
    protected $transaction;
    protected $upload;
    protected $account;
    protected $income_category;
    protected $payment_method;
    protected $user;
    protected $supplier;
    protected $expense_category;
    protected $module = 'transaction';

    /**
     * Instantiate a new instance.
     *
     * @return void
     */
    public function __construct(
        Transaction $transaction,
        UploadRepository $upload,
        IncomeCategoryRepository $income_category,
        PaymentMethodRepository $payment_method,
        UserRepository $user,
        SupplierRepository $supplier,
        ExpenseCategoryRepository $expense_category
    ) {
        $this->transaction = $transaction;
        $this->upload = $upload;
        $this->income_category = $income_category;
        $this->payment_method = $payment_method;
        $this->user = $user;
        $this->supplier = $supplier;
        $this->expense_category = $expense_category;
    }

    /**
     * Get transaction query
     *
     * @return Transaction query
     */
    public function getQuery()
    {
        return $this->transaction->with('account', 'customer', 'customer.profile', 'supplier', 'paymentMethod', 'currency', 'incomeCategory', 'expenseCategory', 'fromAccount');
    }

    /**
     * Count transaction
     *
     * @return integer
     */
    public function count()
    {
        return $this->transaction->count();
    }

    /**
     * Get all transactions
     *
     * @return array
     */
    public function getAll()
    {
        return $this->transaction->with('account', 'customer', 'customer.profile', 'supplier', 'paymentMethod', 'currency', 'incomeCategory', 'expenseCategory', 'fromAccount')->all();
    }

    /**
     * Get account last transaction
     *
     * @return array
     */

    public function getLastTransactionByAccount($account_id)
    {
        return $this->transaction->whereAccountId($account_id)->orWhere('from_account_id',$account_id)->orderBy('date','desc')->first();
    }

    /**
     * Find transaction with given id or throw an error.
     *
     * @param integer $id
     * @return Transaction
     */
    public function findOrFail($id)
    {
        $transaction = $this->transaction->with('account', 'customer', 'customer.profile', 'supplier', 'paymentMethod', 'currency', 'incomeCategory', 'expenseCategory', 'fromAccount')->filterByStatus(1)->filterById($id)->first();

        if (! $transaction) {
            throw ValidationException::withMessages(['message' => trans('transaction.could_not_find')]);
        }

        return $transaction;
    }

    /**
     * Find transaction with given uuid or throw an error.
     *
     * @param string $uuid
     * @return Transaction
     */
    public function findByUuidOrFail($uuid, $type = null)
    {
        $transaction = $this->transaction->with('account', 'customer', 'customer.profile', 'supplier', 'paymentMethod', 'currency', 'incomeCategory', 'expenseCategory', 'fromAccount')->filterByUuid($uuid)->filterByHead($type)->filterByStatus(1)->first();

        if (! $transaction) {
            throw ValidationException::withMessages(['message' => trans('transaction.could_not_find')]);
        }

        return $transaction;
    }

    public function findByTokenOrFail($token)
    {
        $transaction = $this->transaction->filterByToken($token)->first();
        
        if (! $transaction) {
            throw ValidationException::withMessages(['message' => trans('transaction.could_not_find')]);
        }

        return $transaction;
    }

    /**
     * Find transaction with given id for given invoice or throw an error.
     *
     * @param integer $id
     * @param integer $invoice_id
     * @return Transaction
     */
    public function findByIdAndInvoiceIdOrFail($id, $invoice_id)
    {
        $transaction = $this->transaction->with('account', 'customer', 'customer.profile', 'paymentMethod', 'currency', 'incomeCategory')->filterById($id)->filterByInvoiceId($invoice_id)->filterByStatus(1)->first();

        if (! $transaction) {
            throw ValidationException::withMessages(['message' => trans('transaction.could_not_find')]);
        }

        return $transaction;
    }

    /**
     * Find transaction with given uuid for given invoice or throw an error.
     *
     * @param string $uuid
     * @param integer $invoice_id
     * @return Transaction
     */
    public function findByUuidAndInvoiceIdOrFail($uuid, $invoice_id)
    {
        $transaction = $this->transaction->with('account', 'customer', 'customer.profile', 'paymentMethod', 'currency', 'incomeCategory','invoice','invoice.customer','invoice.customer.profile')->filterByUuid($uuid)->filterByInvoiceId($invoice_id)->filterByStatus(1)->first();

        if (! $transaction) {
            throw ValidationException::withMessages(['message' => trans('transaction.could_not_find')]);
        }

        return $transaction;
    }

    public function getInvoicePaidAmount($invoice_id)
    {
        $paid_transactions = $this->transaction->filterByInvoiceId($invoice_id)->filterByStatus(1)->get();
        return ($paid_transactions) ? $paid_transactions->sum(function($transaction) {
            return $transaction->amount + $transaction->coupon_discount;
        }) : 0;
    }

    public function getInvoiceLastPaymentDate($invoice_id)
    {
        $last_transaction = $this->transaction->filterByInvoiceId($invoice_id)->filterByStatus(1)->orderBy('created_at','desc')->first();

        return $last_transaction ? $last_transaction->date : null;
    }

    /**
     * Paginate all transactions using given params.
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($params, $type = null)
    {
        $sort_by             = isset($params['sort_by']) ? $params['sort_by'] : 'created_at';
        $order               = isset($params['order']) ? $params['order'] : 'desc';
        $page_length         = isset($params['page_length']) ? $params['page_length'] : config('config.page_length');
        $income_category_id  = isset($params['income_category_id']) ? $params['income_category_id'] : '';
        $expense_category_id = isset($params['expense_category_id']) ? $params['expense_category_id'] : '';
        $payment_method_id   = isset($params['payment_method_id']) ? $params['payment_method_id'] : '';
        $account_id          = isset($params['account_id']) ? $params['account_id'] : '';
        $from_account_id     = isset($params['from_account_id']) ? $params['from_account_id'] : '';
        $supplier_id         = isset($params['supplier_id']) ? $params['supplier_id'] : '';
        $customer_id         = isset($params['customer_id']) ? $params['customer_id'] : '';
        $currency_id         = isset($params['currency_id']) ? $params['currency_id'] : '';
        $invoice_id          = isset($params['invoice_id']) ? $params['invoice_id'] : '';
        $reference_number    = isset($params['reference_number']) ? $params['reference_number'] : '';
        $start_date          = isset($params['start_date']) ? $params['start_date'] : '';
        $end_date            = isset($params['end_date']) ? $params['end_date'] : '';

        return $this->transaction->with('account', 'customer', 'customer.profile', 'supplier', 'paymentMethod', 'currency', 'incomeCategory', 'expenseCategory', 'fromAccount')->filterByHead($type)->filterByIncomeCategoryId($income_category_id)->filterByExpenseCategoryId($expense_category_id)->filterByPaymentMethodId($payment_method_id)->filterByAccountId($account_id)->filterByFromAccountId($from_account_id)->filterByCustomerId($customer_id)->filterBySupplierId($supplier_id)->filterByCurrencyId($currency_id)->filterByReferenceNumber($reference_number)->filterByInvoiceId($invoice_id)->dateBetween([
                'start_date' => $start_date,
                'end_date' => $end_date
            ])->filterByStatus(1)->orderBy($sort_by, $order)->paginate($page_length);
    }

    /**
     * Validate transaction type.
     *
     * @param string $type
     * @return string
     */
    public function validateType($type = 'expense')
    {
        if (! in_array($type, ['expense','income', 'account-transfer'])) {
            return  'expense';
        }

        return $type;
    }

    /**
     * Create a new transaction.
     *
     * @param array $params
     * @return Transaction
     */
    public function create($params)
    {
        $type = isset($params['type']) ? $params['type'] : null;

        $this->validateInputId($params, $type);

        $transaction = $this->transaction->forceCreate($this->formatParams($params));

        $upload_token = isset($params['upload_token']) ? $params['upload_token'] : null;

        $this->upload->store($this->module, $transaction->id, $upload_token);

        return $transaction;
    }

    /**
     * Create a new customer transaction.
     *
     * @param array $params
     * @return Transaction
     */
    public function createCustomerTransaction($params)
    {
        $transaction = $this->transaction->forceCreate($this->formatParams($params));

        return $transaction;
    }

    /**
     * Validate input ids.
     *
     * @param array $params
     * @return null
     */

    public function validateInputId($params, $type)
    {
        $account_ids = \App\Account::all()->pluck('id')->all();
        $income_category_ids = $this->income_category->listId();
        $expense_category_ids = $this->expense_category->listId();
        $payment_method_ids = $this->payment_method->listId();
        $customer_ids = $this->user->listCustomerId();
        $supplier_ids = $this->supplier->listId();
        $currency_ids = \App\Currency::all()->pluck('id')->all();

        $account_id = isset($params['account_id']) ? $params['account_id'] : null;
        $transaction_category_id = isset($params['transaction_category_id']) ? $params['transaction_category_id'] : null;
        $from_account_id = isset($params['from_account_id']) ? $params['from_account_id'] : null;
        $currency_id = isset($params['currency_id']) ? $params['currency_id'] : null;
        $customer_id = isset($params['customer_id']) ? $params['customer_id'] : null;
        $supplier_id = isset($params['supplier_id']) ? $params['supplier_id'] : null;
        $payment_method_id = isset($params['payment_method_id']) ? $params['payment_method_id'] : null;

        if (! in_array($account_id, $account_ids)) {
            throw ValidationException::withMessages(['message' => trans('account.could_not_find')]);
        }

        if (! in_array($payment_method_id, $payment_method_ids)) {
            throw ValidationException::withMessages(['message' => trans('payment.could_not_find_payment_method')]);
        }

        if (! in_array($currency_id, $currency_ids)) {
            throw ValidationException::withMessages(['message' => trans('currency.could_not_find')]);
        }

        if ($type === 'income' && ! in_array($transaction_category_id, $income_category_ids)) {
            throw ValidationException::withMessages(['message' => trans('transaction.could_not_find_transaction_category',['type' => trans('transaction.'.$type)])]);
        }

        if ($type === 'expense' && ! in_array($transaction_category_id, $expense_category_ids)) {
            throw ValidationException::withMessages(['message' => trans('transaction.could_not_find_transaction_category',['type' => trans('transaction.'.$type)])]);
        }

        if ($type === 'account-transfer' && ! in_array($from_account_id, $account_ids)) {
            throw ValidationException::withMessages(['message' => trans('account.could_not_find')]);
        }

        if ($type === 'income' && ! in_array($customer_id, $customer_ids)) {
            throw ValidationException::withMessages(['message' => trans('user.could_not_find')]);
        }

        if ($type === 'expense' && ! in_array($supplier_id, $supplier_ids)) {
            throw ValidationException::withMessages(['message' => trans('supplier.could_not_find')]);
        }
    }

    /**
     * Prepare given params for inserting into database.
     *
     * @param array $params
     * @param string $type
     * @return array
     */
    private function formatParams($params, $action = 'create')
    {
        $type = isset($params['type']) ? $params['type'] : null;

        $formatted = [
            'expense_category_id' => ($type === 'expense') ? (isset($params['transaction_category_id']) ? $params['transaction_category_id'] : null) : null,
            'income_category_id'  => ($type === 'income') ? (isset($params['transaction_category_id']) ? $params['transaction_category_id'] : null) : null,
            'account_id'          => isset($params['account_id']) ? $params['account_id'] : null,
            'from_account_id'     => ($type === 'account-transfer') ? (isset($params['from_account_id']) ? $params['from_account_id'] : null) : null,
            'customer_id'         => ($type === 'income' || ! $type) ? (isset($params['customer_id']) ? $params['customer_id'] : null) : null,
            'supplier_id'         => ($type === 'expense') ? (isset($params['supplier_id']) ? $params['supplier_id'] : null) : null,
            'currency_id'         => isset($params['currency_id']) ? $params['currency_id'] : null,
            'conversion_rate'     => isset($params['conversion_rate']) ? $params['conversion_rate'] : 0,
            'amount'              => isset($params['amount']) ? $params['amount'] : 0,
            'date'                => isset($params['date']) ? toDate($params['date']) : null,
            'payment_method_id'   => isset($params['payment_method_id']) ? $params['payment_method_id'] : null,
            'reference_number'    => isset($params['reference_number']) ? $params['reference_number'] : null,
            'description'         => isset($params['description']) ? $params['description'] : null,
            'invoice_id'          => isset($params['invoice_id']) ? $params['invoice_id'] : null,
            'status'              => 1,
            'address_line_1'      => isset($params['address_line_1']) ? $params['address_line_1'] : null,
            'address_line_2'      => isset($params['address_line_2']) ? $params['address_line_2'] : null,
            'city'                => isset($params['city']) ? $params['city'] : null,
            'state'               => isset($params['state']) ? $params['state'] : null,
            'zipcode'             => isset($params['zipcode']) ? $params['zipcode'] : null,
            'country'             => isset($params['country']) ? $params['country'] : null,
            'phone'               => isset($params['phone']) ? $params['phone'] : null
        ];

        if ($action === 'create') {
            $formatted['head'] = $type;
            $formatted['token'] = strtoupper(randomString(25));
            $formatted['upload_token'] = isset($params['upload_token']) ? $params['upload_token'] : null;
            $formatted['uuid'] = Str::uuid();
            $formatted['user_id'] = \Auth::user()->id;
        }

        return $formatted;
    }

    /**
     * Check given transaction is editable or not.
     *
     * @param Transaction $transaction
     *
     * @return void
     */
    private function isEditable($transaction)
    {
        if ($transaction->source) {
            throw ValidationException::withMessages(['message' => trans('general.permission_denied')]);
        }
    }

    /**
     * Update given transaction.
     *
     * @param Transaction $transaction
     * @param array $params
     *
     * @return Transaction
     */
    public function update(Transaction $transaction, $params)
    {
        $this->isEditable($transaction);

        $type = isset($params['type']) ? $params['type'] : null;

        $this->validateInputId($params, $type);

        $transaction->forceFill($this->formatParams($params, 'update'))->save();

        $upload_token = isset($params['upload_token']) ? $params['upload_token'] : null;

        $this->upload->update($this->module, $transaction->id, $upload_token);

        return $transaction;
    }

    /**
     * Check given transaction is editable or not.
     *
     * @param Transaction $transaction
     *
     * @return void
     */
    public function deletable($uuid, $type)
    {
        $transaction = $this->findByUuidOrFail($uuid, $type);

        $this->isEditable($transaction);

        return $transaction;
    }

    /**
     * Delete transaction.
     *
     * @param integer $id
     * @return bool|null
     */
    public function delete(Transaction $transaction)
    {
        $this->isEditable($transaction);

        return $transaction->delete();
    }

    /**
     * Delete multiple transactions.
     *
     * @param array $ids
     * @return bool|null
     */
    public function deleteMultiple($ids)
    {
        return $this->transaction->whereIn('id', $ids)->delete();
    }

    public function graphByCategory()
    {
        $categories = array();
        foreach($this->transaction->select('head')->get() as $transaction)
            if($transaction->head)
                array_push($categories,$transaction->head);

        $transaction_categories = array();
        foreach(array_count_values($categories) as $key => $value)
            $transaction_categories[] = array('name' => $key,'total' => $value);

        return $transaction_categories;
    }
}
