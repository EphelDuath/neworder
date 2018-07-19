<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Repositories\TodoRepository;
use App\Repositories\UserRepository;
use App\Repositories\AccountRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\CurrencyRepository;
use App\Repositories\QuotationRepository;
use App\Repositories\ActivityLogRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\AnnouncementRepository;

class HomeController extends Controller
{
    protected $user;
    protected $activity;
    protected $todo;
    protected $invoice;
    protected $quotation;
    protected $transaction;
    protected $currency;
    protected $account;
    protected $announcement;


    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(
        UserRepository $user,
        ActivityLogRepository $activity,
        TodoRepository $todo,
        InvoiceRepository $invoice,
        QuotationRepository $quotation,
        TransactionRepository $transaction,
        CurrencyRepository $currency,
        AccountRepository $account,
        AnnouncementRepository $announcement
    ) {
        $this->user     = $user;
        $this->activity = $activity;
        $this->todo     = $todo;
        $this->invoice = $invoice;
        $this->quotation = $quotation;
        $this->transaction = $transaction;
        $this->currency = $currency;
        $this->account = $account;
        $this->announcement = $announcement;
    }

    /**
     * Used to test web route
     */
    public function test()
    {
    }

    /**
     * Used to get Dashboard statistics
     */
    public function dashboard()
    {
        if (\Auth::user()->hasRole(config('system.default_role.admin'))) {
            $stats = array(
                'customers'    => $this->user->countFilterbyCustomer(),
                'invoices'     => $this->invoice->count(),
                'quotations'   => $this->quotation->count(),
                'transactions' => $this->transaction->count()
            );

            $accounts = $this->account->getAccountSummary();
        }

        $activity_logs = (config('config.activity_log')) ? $this->activity->getAccessibleUserActivityLog() : [];

        $announcements = (config('config.announcement')) ? $this->announcement->getUserAnnouncement() : [];

        $graph['invoice_status'] = generateDoughnutGraphData($this->invoice->graphByStatus(),trans('invoice.status'));
        $graph['quotation_status'] = generateDoughnutGraphData($this->quotation->graphByStatus(),trans('quotation.status'));
        $graph['transaction_category'] = generateDoughnutGraphData($this->transaction->graphByCategory(),trans('transaction.transaction_category'));

        return $this->success(compact('stats', 'accounts', 'activity_logs', 'announcements','graph'));
    }
}
