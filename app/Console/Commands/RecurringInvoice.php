<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\InvoiceRepository;
use App\Repositories\ConfigurationRepository;

class RecurringInvoice extends Command
{
    protected $invoice;
    protected $config;

    /**
     * Generate & Send Recurring Invoices
     *
     * @var string
     */
    protected $signature = 'recurring-invoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate & Send Recurring Invoices';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(InvoiceRepository $invoice, ConfigurationRepository $config)
    {
        $this->invoice = $invoice;
        $this->config = $config;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->config->setDefault();

        if (isTestMode()) {
            $this->error(trans('general.restricted_test_mode_action'));
            return;
        }

        $recurring_invoices = $this->invoice->getRecurringInvoiceByDate();
        foreach ($recurring_invoices as $recurring_invoice) {
            $new_invoice = $this->invoice->copy($recurring_invoice);

            $new_invoice->date = date('Y-m-d');

            if ($new_invoice->due_date === 'no_due_date') {
                $new_invoice->due_date_detail = null;
            } elseif ($new_invoice->due_date === 'due_on_date') {
                $new_invoice->due_date_detail = date('Y-m-d', strtotime(date('Y-m-d') . ' +'.dateDiff($recurring_invoice->due_date_detail, $recurring_invoice->date).' day'));
            } else {
                $new_invoice->due_date_detail = date('Y-m-d', strtotime(date('Y-m-d') . ' +'.$recurring_invoice->due_date.' day'));
            }

            $new_invoice->is_draft = 0;
            $new_invoice->save();

            $this->invoice->send($new_invoice);

            $recurring_invoice = $this->invoice->updateNextRecurringDate($recurring_invoice);
        }
    }
}
