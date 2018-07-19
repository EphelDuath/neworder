<?php

namespace App\Http\Controllers;

use App\Invoice;
use Illuminate\Http\Request;
use App\Repositories\ItemRepository;
use App\Repositories\UserRepository;
use App\Http\Requests\InvoiceRequest;
use App\Repositories\UploadRepository;
use App\Repositories\AccountRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\CurrencyRepository;
use App\Repositories\EmailLogRepository;
use App\Http\Requests\InvoiceEmailRequest;
use App\Repositories\ActivityLogRepository;
use App\Repositories\TransactionRepository;
use App\Http\Requests\InvoicePaymentRequest;
use App\Repositories\ConfigurationRepository;
use App\Repositories\PaymentMethodRepository;
use App\Http\Requests\InvoiceRecurringRequest;
use App\Repositories\IncomeCategoryRepository;

class InvoiceController extends Controller
{
    protected $request;
    protected $repo;
    protected $activity;
    protected $user;
    protected $currency;
    protected $item;
    protected $email_log;
    protected $account;
    protected $payment_method;
    protected $income_category;
    protected $transaction;
    protected $upload;
    protected $config;
    protected $module = 'invoice';

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(
        Request $request,
        InvoiceRepository $repo,
        ActivityLogRepository $activity,
        UserRepository $user,
        CurrencyRepository $currency,
        ItemRepository $item,
        EmailLogRepository $email_log,
        AccountRepository $account,
        PaymentMethodRepository $payment_method,
        IncomeCategoryRepository $income_category,
        TransactionRepository $transaction,
        UploadRepository $upload,
        ConfigurationRepository $config
    ) {
        $this->request         = $request;
        $this->repo            = $repo;
        $this->activity        = $activity;
        $this->user            = $user;
        $this->currency        = $currency;
        $this->item            = $item;
        $this->email_log       = $email_log;
        $this->account         = $account;
        $this->payment_method  = $payment_method;
        $this->income_category = $income_category;
        $this->transaction     = $transaction;
        $this->upload          = $upload;
        $this->config          = $config;
    }

    /**
     * Used to fetch Pre-Requisites for Invoice
     * @get ("/api/invoice/pre-requisite")
     * @return Response
     */
    public function preRequisite()
    {
        $this->authorize('preRequisite', Invoice::class);

        $customers          = $this->user->selectAllCustomerByNameId();
        $currencies         = $this->currency->selectAllForCurrencyInput();
        $default_currency   = $this->currency->default();
        $items              = generateSelectOption($this->item->listAll());
        $item_details       = $this->item->getAll();
        $item_types         = generateTranslatedSelectOption(getVar('list')['item_type']);
        $default_item_type  = ['name' => trans('list.quantity'), 'id' => 'quantity'];
        $due_dates          = $this->repo->listDueDate();
        $new_invoice_number = $this->repo->getNewInvoiceNumber();

        return $this->success(compact('customers', 'currencies', 'default_currency', 'items', 'item_details', 'item_types', 'default_item_type', 'due_dates', 'new_invoice_number'));
    }

    /**
     * Used to get all Invoices
     * @get ("/api/invoice")
     * @return Response
     */
    public function index()
    {
        $this->authorize('list', Invoice::class);

        return $this->ok($this->repo->paginate($this->request->all()));
    }

    /**
     * Used to store Invoice
     * @post ("/api/invoice")
     * @param ({
     *      @Parameter("uuid", type="string", required="optional", description="Required when update")
     * })
     * @return Response
     */
    public function store(InvoiceRequest $request, $uuid = null)
    {
        $this->authorize($uuid ? 'update' : 'create', Invoice::class);

        $invoice = $this->repo->store($this->request->all(), $uuid ? : null);

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $invoice->id,
            'sub_module' => $invoice->invoice_number,
            'activity'   => 'added'
        ]);

        return $this->success(['message' => trans('invoice.saved'), 'invoice' => $invoice]);
    }

    /**
     * Used to get Invoice detail
     * @get ("/api/invoice/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Invoice"),
     * })
     * @return Response
     */
    public function show($uuid)
    {
        $this->authorize('view', Invoice::class);

        $invoice = $this->repo->findByUuidOrFail($uuid);

        $this->repo->accessible($invoice);

        $attachments =  $this->upload->getAttachment($this->module, $invoice->id);
        $countries = getVar('country');

        return $this->success(compact('invoice', 'attachments', 'countries'));
    }

    /**
     * Used to fetch Invoice detail
     * @get ("/api/invoice/{uuid}/detail")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Invoice"),
     * })
     * @return Response
     */
    public function fetchDetail($uuid)
    {
        $this->authorize('update', Invoice::class);

        $invoice = $this->repo->findByUuidOrFail($uuid);

        $this->repo->isCancelled($invoice);

        $this->repo->accessible($invoice);

        $rows = array();
        foreach ($invoice->invoiceItem as $invoice_item) {
            $rows[] = array(
                'uuid'             => $invoice_item->uuid,
                'name'             => ($invoice_item->item_id) ? : '',
                'selected_item'    => ($invoice_item->item_id) ? ['name' => $invoice_item->Item->detail,'id' => $invoice_item->item_id] : [],
                'custom_name'      => ($invoice_item->item_id) ? '' : $invoice_item->name,
                'show_custom_item' => ($invoice_item->item_id) ? false : true,
                'quantity'         => formatNumber($invoice_item->quantity, config('config.invoice_line_item_quantity_decimal_place')),
                'unit_price'       => formatNumber($invoice_item->unit_price, $invoice->Currency->decimal_place),
                'discount'         => formatNumber($invoice_item->discount, config('config.invoice_line_item_discount_decimal_place')),
                'tax'              => formatNumber($invoice_item->tax, config('config.invoice_line_item_tax_decimal_place')),
                'amount'           => formatNumber($invoice_item->amount, $invoice->Currency->decimal_place),
                'description'      => $invoice_item->description
            );
        }

        $selected_currency  = $this->currency->selectAllForCurrencyInput()->find($invoice->currency_id);
        $selected_customer  = $invoice->Customer ? ['id' => $invoice->customer_id, 'name' => $invoice->Customer->name] : [];
        $selected_item_type = ['id' => $invoice->item_type, 'name' => trans('list.'.$invoice->item_type)];

        if ($invoice->due_date === -1) {
            $selected_due_date_name = trans('invoice.no_due_date');
        } elseif ($invoice->due_date === 0) {
            $selected_due_date_name = trans('invoice.due_on_invoice_date');
        } elseif ($invoice->due_date === 'due_on_date') {
            $selected_due_date_name = trans('invoice.due_on_date');
        } else {
            $selected_due_date_name = trans('invoice.due_in_days', ['day' => $invoice->due_date]);
        }

        $selected_due_date = ['id' => $invoice->due_date, 'name' => $selected_due_date_name];

        return $this->success(compact('invoice', 'rows', 'selected_currency', 'selected_customer', 'selected_item_type', 'selected_due_date'));
    }

    /**
     * Used to send Email
     * @post ("/api/invoice/{uuid}/email")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Invoice"),
     *      @Parameter("body", type="text", required="true", description="Body of Email"),
     *      @Parameter("subject", type="string", required="true", description="Subject of Email"),
     *      @Parameter("template_id", type="integer", required="true", description="Template Id of Email"),
     * })
     * @return Response
     */
    public function sendEmail(InvoiceEmailRequest $request, $uuid)
    {
        $this->authorize('update', Invoice::class);

        $invoice = $this->repo->findByUuidOrFail($uuid);

        $this->repo->isCancelled($invoice);

        $this->repo->isDraft($invoice);

        $this->repo->accessible($invoice);

        $this->repo->sendMail($invoice, $this->request->all());

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $invoice->id,
            'sub_module' => 'mail',
            'activity'   => 'sent'
        ]);

        return $this->success(['message' => trans('template.mail_sent')]);
    }

    /**
     * Used to get Email log of Invoice
     * @get ("/api/invoice/{uuid}/email-log")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Invoice"),
     * })
     * @return Response
     */
    public function emailLog($uuid)
    {
        $this->authorize('update', Invoice::class);

        $invoice = $this->repo->findByUuidOrFail($uuid);

        $this->repo->isCancelled($invoice);

        $this->repo->isDraft($invoice);

        $this->repo->accessible($invoice);

        return $this->ok($this->email_log->paginate([
            'module'      => $this->module,
            'module_id'   => $invoice->id,
            'page_length' => request('page_length')
        ]));
    }

    /**
     * Used to get Email Detail of Invoice email log
     * @post ("/api/invoice/{uuid}/email-detail")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Invoice"),
     *      @Parameter("Id", type="string", required="true", description="Id of Email Log"),
     * })
     * @return Response
     */
    public function emailDetail($uuid)
    {
        $this->authorize('update', Invoice::class);

        $invoice = $this->repo->findByUuidOrFail($uuid);

        $this->repo->isCancelled($invoice);

        $this->repo->isDraft($invoice);

        $this->repo->accessible($invoice);

        return $this->ok($this->email_log->findByModuleAndModuleIdOrFail(request('id'), $this->module, $invoice->id));
    }

    /**
     * Used to send Invoice
     * @post ("/api/invoice/{uuid}/send")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Invoice"),
     * })
     * @return Response
     */
    public function send($uuid)
    {
        $this->authorize('update', Invoice::class);

        $invoice = $this->repo->findByUuidOrFail($uuid);

        $this->repo->isCancelled($invoice);

        $this->repo->accessible($invoice);

        $invoice = $this->repo->send($invoice);

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $invoice->id,
            'sub_module' => $invoice->invoice_number,
            'activity'   => 'sent'
        ]);

        return $this->success(['message' => trans('invoice.sent')]);
    }

    /**
     * Used to cancel Invoice
     * @post ("/api/invoice/{uuid}/cancel")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Invoice"),
     * })
     * @return Response
     */
    public function cancel($uuid)
    {
        $this->authorize('update', Invoice::class);

        $invoice = $this->repo->findByUuidOrFail($uuid);

        $this->repo->isCancelled($invoice);

        $this->repo->isDraft($invoice);

        $this->repo->accessible($invoice);

        $invoice = $this->repo->cancel($invoice);

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $invoice->id,
            'sub_module' => $invoice->invoice_number,
            'activity'   => 'cancelled'
        ]);

        return $this->success(['message' => trans('invoice.cancelled')]);
    }

    /**
     * Used to undo cancel Invoice
     * @post ("/api/invoice/{uuid}/undo-cancel")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Invoice"),
     * })
     * @return Response
     */
    public function undoCancel($uuid)
    {
        $this->authorize('update', Invoice::class);

        $invoice = $this->repo->findByUuidOrFail($uuid);

        $this->repo->isDraft($invoice);

        $this->repo->accessible($invoice);

        $invoice = $this->repo->undoCancel($invoice);

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $invoice->id,
            'sub_module' => $invoice->invoice_number,
            'activity'   => 'undo_cancelled'
        ]);

        return $this->success(['message' => trans('invoice.saved')]);
    }

    /**
     * Used to print Invoice
     * @get ("/invoice/{uuid}/print")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Invoice"),
     * })
     * @return Response
     */
    public function print($uuid)
    {
        $invoice = $this->repo->findByUuidOrFail($uuid);

        $this->repo->accessible($invoice);

        $action = 'print';
        $invoice_color = $this->repo->getColor($invoice);
        $invoice_status = $this->repo->getPrintStatus($invoice);

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $invoice->id,
            'sub_module' => $invoice->invoice_number,
            'activity'   => 'printed'
        ]);
        
        return view('invoice.print', compact('invoice', 'action', 'invoice_color','invoice_status'));
    }

    /**
     * Used to generate PDF Invoice
     * @get ("/invoice/{uuid}/print")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Invoice"),
     * })
     * @return Response
     */
    public function pdf($uuid)
    {
        $invoice = $this->repo->findByUuidOrFail($uuid);

        $this->repo->accessible($invoice);

        $action = 'pdf';
        $invoice_color = $this->repo->getColor($invoice);
        $invoice_status = $this->repo->getPrintStatus($invoice);

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $invoice->id,
            'sub_module' => $invoice->invoice_number,
            'activity'   => 'saved_as_pdf'
        ]);

        $pdf = \PDF::loadView('invoice.print', compact('invoice', 'invoice_color', 'action','invoice_status'));
        return $pdf->download($uuid.'.pdf');
    }

    /**
     * Used to mark Invoice as sent
     * @post ("/api/invoice/{uuid}/mark-as-sent")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Invoice"),
     * })
     * @return Response
     */
    public function markAsSent($uuid)
    {
        $this->authorize('update', Invoice::class);

        $invoice = $this->repo->findByUuidOrFail($uuid);

        $this->repo->isCancelled($invoice);

        $this->repo->accessible($invoice);

        $invoice = $this->repo->markAsSent($invoice);

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $invoice->id,
            'sub_module' => $invoice->invoice_number,
            'activity'   => 'marked_as_sent'
        ]);

        return $this->success(['message' => trans('invoice.saved')]);
    }

    /**
     * Used to copy Invoice
     * @post ("/api/invoice/{uuid}/copy")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Invoice"),
     * })
     * @return Response
     */
    public function copy($uuid)
    {
        $this->authorize('update', Invoice::class);

        $invoice = $this->repo->findByUuidOrFail($uuid);

        $this->repo->accessible($invoice);

        $new_invoice = $this->repo->copy($invoice);

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $new_invoice->id,
            'sub_module' => $invoice->invoice_number,
            'activity'   => 'copied'
        ]);

        return $this->success(['message' => trans('invoice.copied'),'uuid' => $new_invoice->uuid]);
    }

    /**
     * Used to delete Invoice
     * @delete ("/api/invoice/{uuid}")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Invoice"),
     * })
     * @return Response
     */
    public function destroy($uuid)
    {
        $this->authorize('update', Invoice::class);

        $invoice = $this->repo->deletable($uuid);

        $this->repo->accessible($invoice);

        $this->upload->delete($this->module, $invoice->id);

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $invoice->id,
            'sub_module' => $invoice->invoice_number,
            'activity'   => 'deleted'
        ]);

        $this->repo->delete($invoice);

        return $this->success(['message' => trans('invoice.deleted')]);
    }

    /**
     * Used to download Invoice attachment
     * @delete ("/invoice/{invoice_uuid}/attachment/{attachment_uuid}/download")
     * @param ({
     *      @Parameter("invoice_uuid", type="string", required="true", description="Unique Id of Invoice"),
     *      @Parameter("attachment_uuid", type="string", required="true", description="Unique Id of Attachment"),
     * })
     * @return Response
     */
    public function download($invoice_uuid, $attachment_uuid)
    {
        $invoice = $this->repo->findByUuidOrFail($invoice_uuid);

        $this->repo->accessible($invoice);

        $attachment =  $this->upload->getAttachment($this->module, $invoice->id, $attachment_uuid);

        try {
            \Storage::exists($attachment->filename);
        } catch (\Exception $e) {
        }

        $this->activity->record([
            'module'        => 'attachment',
            'module_id'     => $attachment->id,
            'sub_module'    => $this->module,
            'sub_module_id' => $invoice->id,
            'activity'      => 'downloaded'
        ]);

        $download_path = storage_path('app/'.$attachment->filename);
        return response()->download($download_path, $attachment->user_filename);
    }

    /**
     * Used to toggle Partial Payment in Invoice
     * @post ("/api/invoice/{uuid}/toggle-partial-payment")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Invoice"),
     * })
     * @return Response
     */
    public function togglePartialPayment($uuid)
    {
        $this->authorize('update', Invoice::class);

        $invoice = $this->repo->findByUuidOrFail($uuid);

        $this->repo->isCancelled($invoice);

        $this->repo->accessible($invoice);

        $this->repo->togglePartialPayment($invoice);

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $invoice->id,
            'sub_module' => $invoice->invoice_number,
            'activity'   => 'partial_payment_'.($invoice->partial_payment ? 'enabled' : 'disabled')
        ]);

        return $this->success(['message' => trans('invoice.saved')]);
    }

    /**
     * Used to update Invoice recurrence
     * @post ("/api/invoice/{uuid}/recurrence")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Invoice"),
     *      @Parameter("is_recurring", type="boolean", required="true", description="Is invoice recurring?"),
     *      @Parameter("recurrence_start_date", type="date", required_if="is_recurring = true", description="Start date of Recurrence"),
     *      @Parameter("recurrence_end_date", type="date", required_if="is_recurring = true", description="End date of Recurrence"),
     *      @Parameter("recurring_frequency", type="integer", required_if="is_recurring = true", description="Frequency of Recurrence"),
     * })
     * @return Response
     */
    public function recurring(InvoiceRecurringRequest $request, $uuid)
    {
        $this->authorize('update', Invoice::class);

        $invoice = $this->repo->findByUuidOrFail($uuid);

        $this->repo->isCancelled($invoice);

        $this->repo->isDraft($invoice);

        $this->repo->accessible($invoice);

        $invoice = $this->repo->recurring($invoice, $this->request->all());

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $invoice->id,
            'sub_module' => $invoice->invoice_number,
            'activity'   => $invoice->is_recurring ? 'enable_recurring' : 'disable_recurring'
        ]);

        return $this->success(['message' => trans('invoice.saved')]);
    }

    /**
     * Used to get Recurring Invoice
     * @get ("/api/invoice/{uuid}/recurring-invoice")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Invoice"),
     * })
     * @return Response
     */
    public function listRecurring($uuid)
    {
        $this->authorize('update', Invoice::class);

        $invoice = $this->repo->findByUuidOrFail($uuid);

        $this->repo->isCancelled($invoice);

        $this->repo->isDraft($invoice);

        $this->repo->accessible($invoice);

        $recurring_frequencies = $this->repo->listRecurringFrequency();

        $selected_recurring_frequency = $invoice->is_recurring ? ['id' => $invoice->recurring_frequency, 'name' => nestedArraySearch('id', $invoice->recurring_frequency, 'name', $recurring_frequencies)] : [];

        $recurring_invoices = $this->repo->paginate([
            'recurring_invoice_id' => $invoice->id,
            'page_length' => request('page_length')
        ]);

        $next_recurring_date = $invoice->next_recurring_date;

        return $this->success(compact('invoice', 'recurring_frequencies', 'selected_recurring_frequency', 'recurring_invoices','next_recurring_date'));
    }

    /**
     * Used to get Invoice payment pre-requisite
     * @post ("/api/invoice/{uuid}/payment/pre-requisite")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Invoice"),
     * })
     * @return Response
     */
    public function paymentPreRequisite($uuid)
    {
        $this->authorize('update', Invoice::class);

        $invoice = $this->repo->findByUuidOrFail($uuid);

        $this->repo->isCancelled($invoice);

        $this->repo->isDraft($invoice);

        $this->repo->accessible($invoice);

        $accounts = $this->account->selectAll();
        $payment_methods = $this->payment_method->selectAllPaymentMethodByType('income');
        $income_categories = $this->income_category->selectAll();
        ;
        $selected_currency = $this->currency->selectAllForCurrencyInput()->find($invoice->currency_id);
        $default_currency = $this->currency->default();
        ;

        return  $this->success(compact('accounts', 'payment_methods', 'income_categories', 'selected_currency', 'default_currency'));
    }

    /**
     * Used to store Invoice payment
     * @post ("/api/invoice/{uuid}/payment")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Invoice"),
     *      @Parameter("amount", type="numeric", required="true", description="Amount of Payment"),
     *      @Parameter("account_id", type="integer", required="true", description="Id of Account"),
     *      @Parameter("payment_method_id", type="integer", required="true", description="Id of Payment Method"),
     *      @Parameter("income_category_id", type="integer", required="true", description="Id of Income Category"),
     *      @Parameter("date", type="integer", required="true", description="Date of Payment"),
     *      @Parameter("conversion_rate", type="numeric", required_if="currency is not default currency", description="Conversion Rate"),
     *      @Parameter("upload_token", type="string", required="true", description="Upload token from Form"),
     *      @Parameter("description", type="numeric", required="optional", description="Description of Payment"),
     *      @Parameter("email", type="numeric", required="optional", description="If true then email will be sent to the customer"),
     * })
     * @return Response
     */
    public function payment(InvoicePaymentRequest $request, $uuid)
    {
        $this->authorize('update', Invoice::class);

        $invoice = $this->repo->findByUuidOrFail($uuid);

        $this->repo->isCancelled($invoice);

        $this->repo->isDraft($invoice);

        $this->repo->accessible($invoice);

        $transaction = $this->repo->payment($invoice, $this->request->all());

        $this->activity->record([
            'module'        => 'transaction',
            'module_id'     => $transaction->id,
            'sub_module'    => $this->module,
            'sub_module_id' => $invoice->id,
            'activity'      => 'added'
        ]);

        return $this->success(['message' => trans('invoice.payment_added')]);
    }

    /**
     * Used to get Invoice payment
     * @get ("/api/invoice/{uuid}/payment")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Invoice"),
     * })
     * @return Response
     */
    public function getPayment($uuid)
    {
        $this->authorize('listPayment', Invoice::class);

        $invoice = $this->repo->findByUuidOrFail($uuid);

        $this->repo->isCancelled($invoice);

        $this->repo->isDraft($invoice);

        $this->repo->accessible($invoice);

        $paid = $this->transaction->getInvoicePaidAmount($invoice->id);
        $payable = $invoice->total;

        $payments = $this->transaction->paginate([
            'invoice_id' => $invoice->id,
            'page_length' => request('page_length')
        ]);

        return $this->success(compact('paid', 'payments', 'payable'));
    }

    /**
     * Used to get Invoice payment detail
     * @get ("/api/invoice/{uuid}/payment/{payment_uuid}")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Invoice"),
     *      @Parameter("payment_uuid", type="string", required="true", description="Unique Id of Payment"),
     * })
     * @return Response
     */
    public function showPayment($uuid, $payment_uuid)
    {
        $this->authorize('listPayment', Invoice::class);

        $invoice = $this->repo->findByUuidOrFail($uuid);

        $this->repo->isCancelled($invoice);

        $this->repo->isDraft($invoice);

        $this->repo->accessible($invoice);

        return $this->ok($this->transaction->findByUuidAndInvoiceIdOrFail($payment_uuid, $invoice->id));
    }

    /**
     * Used to delete Invoice payment
     * @delete ("/api/invoice/{uuid}/payment/{id}")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Invoice"),
     *      @Parameter("id", type="string", required="true", description="Id of Payment"),
     * })
     * @return Response
     */
    public function destroyPayment($uuid, $id)
    {
        $this->authorize('update', Invoice::class);

        $invoice = $this->repo->findByUuidOrFail($uuid);

        $this->repo->isCancelled($invoice);

        $this->repo->isDraft($invoice);

        $this->repo->accessible($invoice);

        $transaction = $this->transaction->findByIdAndInvoiceIdOrFail($id, $invoice->id);

        $transaction = $this->transaction->deletable($transaction->uuid, $transaction->head);

        $this->upload->delete($this->module, $transaction->id);

        $this->activity->record([
            'module'        => 'transaction',
            'module_id'     => $transaction->id,
            'sub_module'    => $this->module,
            'sub_module_id' => $invoice->id,
            'activity'      => 'deleted'
        ]);

        $this->transaction->delete($transaction);

        $this->repo->updateStatus($invoice);

        return $this->success(['message' => trans('invoice.payment_deleted')]);
    }
}
