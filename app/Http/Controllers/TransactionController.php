<?php

namespace App\Http\Controllers;

use App\Transaction;
use Illuminate\Http\Request;
use App\Repositories\UserRepository;
use App\Repositories\UploadRepository;
use App\Repositories\AccountRepository;
use App\Repositories\CurrencyRepository;
use App\Repositories\SupplierRepository;
use App\Http\Requests\TransactionRequest;
use App\Repositories\ActivityLogRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\IncomeCategoryRepository;
use App\Repositories\ExpenseCategoryRepository;

class TransactionController extends Controller
{
    protected $request;
    protected $repo;
    protected $activity;
    protected $account;
    protected $income_category;
    protected $expense_category;
    protected $user;
    protected $payment_method;
    protected $currency;
    protected $supplier;
    protected $upload;
    protected $module = 'transaction';

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(
        Request $request,
        TransactionRepository $repo,
        ActivityLogRepository $activity,
        AccountRepository $account,
        IncomeCategoryRepository $income_category,
        ExpenseCategoryRepository $expense_category,
        UserRepository $user,
        PaymentMethodRepository $payment_method,
        CurrencyRepository $currency,
        SupplierRepository $supplier,
        UploadRepository $upload
    ) {
        $this->request          = $request;
        $this->repo             = $repo;
        $this->activity         = $activity;
        $this->account          = $account;
        $this->income_category  = $income_category;
        $this->expense_category = $expense_category;
        $this->user             = $user;
        $this->payment_method   = $payment_method;
        $this->currency         = $currency;
        $this->supplier         = $supplier;
        $this->upload           = $upload;
    }

    /**
     * Used to get Pre Requisite for Transaction Module
     * @get ("/api/transaction/pre-requisite")
     * @return Response
     */
    public function preRequisite($type = 'expense')
    {
        $type = $this->repo->validateType($type);

        $this->authorize('prerequisite', [Transaction::class, $type]);

        $accounts = $this->account->selectAll();

        $transaction_categories = ($type === 'income') ? $this->income_category->selectAll() : ($type === 'expense' ? $this->expense_category->selectAll() : []);

        $customers = ($type === 'income') ? $this->user->selectAllCustomerByNameId() : [];

        $suppliers = ($type === 'expense') ? $this->supplier->selectAll() : [];

        $payment_methods = ($type === 'income' || $type === 'expense') ? $this->payment_method->selectAllPaymentMethodByType($type) : $this->payment_method->selectAllPaymentMethodByType('account_transfer');

        $currencies = $this->currency->selectAllForCurrencyInput();
        
        $default_currency = $this->currency->default();

        return $this->success(compact('accounts', 'transaction_categories', 'customers', 'payment_methods', 'currencies', 'default_currency', 'suppliers'));
    }

    /**
     * Used to get all Transactions
     * @get ("/api/transaction")
     * @return Response
     */
    public function index($type)
    {
        $this->authorize('list', [Transaction::class, $type]);

        return $this->ok($this->repo->paginate($this->request->all(), $type));
    }

    /**
     * Used to store Transaction
     * @post ("/api/transaction")
     * @param ({
     *      @Parameter("name", type="string", required="true", description="Name of Transaction"),
     *      @Parameter("description", type="text", required="optional", description="Transaction description")
     * })
     * @return Response
     */
    public function store(TransactionRequest $request)
    {
        $this->authorize('create', [Transaction::class, request('type')]);

        $transaction = $this->repo->create($this->request->all(), $this->module);

        $this->activity->record([
            'module'    => request('type'),
            'module_id' => $transaction->id,
            'activity'  => 'added'
        ]);

        return $this->success(['message' => trans('transaction.added', ['type' => trans('transaction.'.request('type'))])]);
    }

    /**
     * Used to get Transaction detail
     * @get ("/api/transaction/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Transaction"),
     * })
     * @return Response
     */
    public function show($type, $uuid)
    {
        $this->authorize('view', [Transaction::class, $type]);

        $transaction = $this->repo->findByUuidOrFail($uuid, $type);

        $selected_account = ['name' => $transaction->Account->name, 'id' => $transaction->account_id];
        $selected_from_account = ($type === 'account-transfer') ? ['name' => $transaction->FromAccount->name, 'id' => $transaction->from_account_id] : [];
        $selected_transaction_category = ($type != 'account-transfer') ? (($type === 'income' ? ['name' => $transaction->IncomeCategory->name, 'id' => $transaction->income_category_id] : ['name' => $transaction->ExpenseCategory->name, 'id' => $transaction->expense_category_id])) : [];
        $selected_payment_method = ['name' => $transaction->PaymentMethod->name, 'id' => $transaction->payment_method_id];
        $selected_currency = $this->currency->selectAllForCurrencyInput()->find($transaction->currency_id);
        $selected_customer = ($type === 'income') ? ['id' => $transaction->customer_id, 'name' => $transaction->Customer->name] : [];
        $selected_supplier = ($type === 'expense') ? ['id' => $transaction->supplier_id, 'name' => $transaction->Supplier->name] : [];

        $attachments = $this->upload->getAttachment($this->module, $transaction->id);

        return $this->success(compact('transaction', 'selected_account', 'selected_from_account', 'selected_transaction_category', 'selected_payment_method', 'selected_customer', 'selected_currency', 'selected_supplier', 'attachments'));
    }

    /**
     * Used to update Transaction
     * @patch ("/api/transaction/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Transaction"),
     *      @Parameter("name", type="string", required="true", description="Name of Transaction"),
     *      @Parameter("description", type="text", required="optional", description="Transaction description")
     * })
     * @return Response
     */
    public function update(TransactionRequest $request, $uuid)
    {
        $this->authorize('update', [Transaction::class, request('type')]);

        $transaction = $this->repo->findByUuidOrFail($uuid, request('type'));
        
        $transaction = $this->repo->update($transaction, $this->request->all(), $this->module);

        $this->activity->record([
            'module'    => request('type'),
            'module_id' => $transaction->id,
            'activity'  => 'updated'
        ]);

        return $this->success(['message' => trans('transaction.updated', ['type' => trans('transaction.'.request('type'))])]);
    }

    /**
     * Used to delete Transaction
     * @delete ("/api/transaction/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Transaction"),
     * })
     * @return Response
     */
    public function destroy($type, $uuid)
    {
        $this->authorize('delete', [Transaction::class, $type]);

        $transaction = $this->repo->deletable($uuid, $type);

        $this->activity->record([
            'module'    => $type,
            'module_id' => $transaction->id,
            'activity'  => 'deleted'
        ]);

        $this->repo->delete($transaction);

        return $this->success(['message' => trans('transaction.deleted', ['type' => trans('transaction.'.$type)])]);
    }
}
