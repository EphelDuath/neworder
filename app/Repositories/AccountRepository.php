<?php
namespace App\Repositories;

use App\Account;
use App\Repositories\CurrencyRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Validation\ValidationException;

class AccountRepository
{
    protected $account;
    protected $currency;
    protected $transaction;

    /**
     * Instantiate a new instance.
     *
     * @return void
     */
    public function __construct(
        Account $account,
        CurrencyRepository $currency,
        TransactionRepository $transaction
    ) {
        $this->account = $account;
        $this->currency = $currency;
        $this->transaction = $transaction;
    }

    /**
     * Get account query
     *
     * @return Account query
     */

    public function getQuery()
    {
        return $this->account;
    }

    /**
     * Count account
     *
     * @return integer
     */

    public function count()
    {
        return $this->account->count();
    }

    /**
     * List all accounts by name & id
     *
     * @return array
     */

    public function listAll()
    {
        return $this->account->all()->pluck('name', 'id')->all();
    }

    /**
     * List all accounts by id
     *
     * @return array
     */
    public function listId()
    {
        return $this->account->all()->pluck('id')->all();
    }

    /**
     * List all accounts by name & id for select option
     *
     * @return array
     */

    public function selectAll()
    {
        return $this->account->all(['name', 'id']);
    }

    /**
     * Get all accounts
     *
     * @return array
     */

    public function getAll()
    {
        return $this->account->all();
    }

    /**
     * Get all account summary
     *
     * @return array
     */
    public function getAccountSummary()
    {
        $default_currency = $this->currency->default();
        $accounts = $this->getAll();
        $data = array();

        foreach($accounts as $account){
            $balance = $account->opening_balance;
            foreach($account->Transaction as $transaction){
                if($transaction->head == 'income' || $transaction->head == 'account-transfer')
                    $balance += $transaction->amount * $transaction->conversion_rate;
                elseif($transaction->head == 'expense')
                    $balance -= $transaction->amount * $transaction->conversion_rate;
            }

            foreach($account->Transfer as $transfer)
                $balance -= $transfer->amount * $transfer->conversion_rate;

            $last_transaction = $this->transaction->getLastTransactionByAccount($account->id);

            $data[] = [
                'name'             => $account->name,
                'type'             => toWord($account->type),
                'balance'          => currency($balance,$default_currency,1),
                'last_transaction' => ($last_transaction) ? showDate($last_transaction->date) : '-'
            ];
        }

        return $data;
    }

    /**
     * Find account with given id or throw an error.
     *
     * @param integer $id
     * @return Account
     */

    public function findOrFail($id)
    {
        $account = $this->account->find($id);

        if (! $account) {
            throw ValidationException::withMessages(['message' => trans('account.could_not_find')]);
        }

        return $account;
    }

    /**
     * Paginate all accounts using given params.
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */

    public function paginate($params)
    {
        $sort_by     = isset($params['sort_by']) ? $params['sort_by'] : 'created_at';
        $order       = isset($params['order']) ? $params['order'] : 'desc';
        $page_length = isset($params['page_length']) ? $params['page_length'] : config('config.page_length');
        $name        = isset($params['name']) ? $params['name'] : '';
        $type        = isset($params['type']) ? $params['type'] : '';

        return $this->account->filterByName($name)->filterByType($type)->orderBy($sort_by, $order)->paginate($page_length);
    }

    /**
     * Create a new account.
     *
     * @param array $params
     * @return Account
     */
    public function create($params)
    {
        return $this->account->forceCreate($this->formatParams($params));
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
        $formatted = [
            'name'            => isset($params['name']) ? $params['name'] : null,
            'opening_balance' => isset($params['opening_balance']) ? $params['opening_balance'] : 0,
            'type'            => isset($params['type']) ? $params['type'] : null,
            'number'          => isset($params['number']) ? $params['number'] : null,
            'bank_name'       => isset($params['bank_name']) ? $params['bank_name'] : null,
            'bank_branch'     => isset($params['bank_branch']) ? $params['bank_branch'] : null,
            'branch_code'     => isset($params['branch_code']) ? $params['branch_code'] : null
        ];

        return $formatted;
    }

    /**
     * Update given account.
     *
     * @param Account $account
     * @param array $params
     *
     * @return Account
     */
    public function update(Account $account, $params)
    {
        $account->forceFill($this->formatParams($params, 'update'))->save();

        return $account;
    }

    /**
     * Find account & check it can be deleted or not.
     *
     * @param integer $id
     * @return Account
     */
    public function deletable($id)
    {
        $account = $this->findOrFail($id);

        if ($account->transaction()->count() || $account->transfer()->count()) {
            throw ValidationException::withMessages(['message' => trans('account.has_many_transactions')]);
        }
        
        return $account;
    }

    /**
     * Delete account.
     *
     * @param integer $id
     * @return bool|null
     */
    public function delete(Account $account)
    {
        return $account->delete();
    }

    /**
     * Delete multiple accounts.
     *
     * @param array $ids
     * @return bool|null
     */
    public function deleteMultiple($ids)
    {
        return $this->account->whereIn('id', $ids)->delete();
    }
}
