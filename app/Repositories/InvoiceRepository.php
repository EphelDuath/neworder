<?php
namespace App\Repositories;

use App\Invoice;
use App\InvoiceItem;
use App\Jobs\SendMail;
use Illuminate\Support\Str;
use App\Jobs\SendInvoiceMail;
use App\Repositories\UserRepository;
use App\Repositories\UploadRepository;
use App\Repositories\CurrencyRepository;
use App\Repositories\EmailLogRepository;
use App\Repositories\DesignationRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Validation\ValidationException;

class InvoiceRepository
{
    protected $invoice;
    protected $designation;
    protected $currency;
    protected $upload;
    protected $invoice_item;
    protected $transaction;
    protected $email;
    protected $user;
    protected $module = 'invoice';

    /**
     * Instantiate a new instance.
     *
     * @return void
     */
    public function __construct(
        Invoice $invoice,
        DesignationRepository $designation,
        CurrencyRepository $currency,
        UploadRepository $upload,
        InvoiceItem $invoice_item,
        TransactionRepository $transaction,
        EmailLogRepository $email,
        UserRepository $user
    ) {
        $this->invoice = $invoice;
        $this->designation = $designation;
        $this->currency = $currency;
        $this->upload = $upload;
        $this->invoice_item = $invoice_item;
        $this->transaction = $transaction;
        $this->email = $email;
        $this->user = $user;
    }

    /**
     * Get invoice query
     *
     * @return Invoice query
     */
    public function getQuery()
    {
        return $this->invoice->with('currency', 'customer', 'customer.profile', 'customer.profile.company', 'invoiceItem', 'invoiceItem.item','invoiceItem.item.itemPrice');
    }

    /**
     * Count invoice
     *
     * @return integer
     */
    public function count()
    {
        return $this->invoice->count();
    }

    /**
     * List all invoices by number & id
     *
     * @return array
     */
    public function listAll()
    {
        return $this->invoice->all()->pluck('number', 'id')->all();
    }

    /**
     * Get all invoices
     *
     * @return array
     */
    public function getAll()
    {
        return $this->invoice->with('currency', 'customer', 'customer.profile', 'customer.profile.company', 'invoiceItem', 'invoiceItem.item','invoiceItem.item.itemPrice')->all();
    }

    /**
     * Get recurring invoices by date
     *
     * @return array
     */
    public function getRecurringInvoiceByDate($date = null)
    {
        $date = ($date) ? : date('Y-m-d');
        return $this->invoice->filterByIsRecurring(1)->filterByNextRecurringDate($date)->get();
    }

    /**
     * Find invoice with given id or throw an error.
     *
     * @param integer $id
     * @return Invoice
     */
    public function findOrFail($id)
    {
        $invoice = $this->invoice->with('currency', 'customer', 'customer.profile', 'customer.profile.company', 'invoiceItem', 'invoiceItem.item','invoiceItem.item.itemPrice')->find($id);

        if (! $invoice) {
            throw ValidationException::withMessages(['message' => trans('invoice.could_not_find')]);
        }

        return $invoice;
    }

    /**
     * Find invoice with given uuid or throw an error.
     *
     * @param string $uuid
     * @return Invoice
     */
    public function findByUuidOrFail($uuid)
    {
        $invoice = $this->invoice->with('currency', 'customer', 'customer.profile', 'customer.profile.company', 'invoiceItem', 'invoiceItem.item','invoiceItem.item.itemPrice')->whereUuid($uuid)->first();

        if (! $invoice) {
            throw ValidationException::withMessages(['message' => trans('invoice.could_not_find')]);
        }

        return $invoice;
    }

    /**
     * List all due dates for invoice.
     *
     * @return array
     */
    public function listDueDate()
    {
        return [
            ['id' => '-1', 'name' => trans('invoice.no_due_date')],
            ['id' => '0', 'name' => trans('invoice.due_on_invoice_date')],
            ['id' => 'due_on_date', 'name' => trans('invoice.due_on_date')],
            ['id' => '2', 'name' => trans('invoice.due_in_days', ['day' => 2])],
            ['id' => '5', 'name' => trans('invoice.due_in_days', ['day' => 5])],
            ['id' => '7', 'name' => trans('invoice.due_in_days', ['day' => 7])],
            ['id' => '10', 'name' => trans('invoice.due_in_days', ['day' => 10])],
            ['id' => '15', 'name' => trans('invoice.due_in_days', ['day' => 15])],
            ['id' => '30', 'name' => trans('invoice.due_in_days', ['day' => 30])],
            ['id' => '45', 'name' => trans('invoice.due_in_days', ['day' => 45])],
            ['id' => '90', 'name' => trans('invoice.due_in_days', ['day' => 90])],
        ];
    }

    /**
     * List all frequencies for recurring invoice.
     *
     * @return array
     */
    public function listRecurringFrequency(){
        return [
            ['id' => '7', 'name' => trans('invoice.weekly')],
            ['id' => '15', 'name' => trans('invoice.fortnightly')],
            ['id' => '30', 'name' => trans('invoice.monthly')],
            ['id' => '60', 'name' => trans('invoice.bi_monthly')],
            ['id' => '90', 'name' => trans('invoice.quarterly')],
            ['id' => '180', 'name' => trans('invoice.bi_annually')],
            ['id' => '365', 'name' => trans('invoice.annually')]
        ];
    }

    /**
     * Get new invoice number.
     *
     * @return string
     */
    public function getNewInvoiceNumber()
    {
        $current_number = ($this->invoice->max('number')) ? : 0;
        $number = (($current_number >= config('config.invoice_start_number')) ? $current_number + 1 : config('config.invoice_start_number'));
        return str_pad($number, config('config.invoice_number_digit'), '0', STR_PAD_LEFT);
    }

    /**
     * Check if invoice is cancelled.
     *
     * @param Invoice $invoice
     * @return string
     */
    public function isCancelled(Invoice $invoice)
    {
        if ($invoice->is_cancelled) {
            throw ValidationException::withMessages(['message' => trans('invoice.invoice_already_cancelled')]);
        }
    }

    /**
     * Check if invoice is draft.
     *
     * @param Invoice $invoice
     * @return string
     */
    public function isDraft(Invoice $invoice)
    {
        if ($invoice->is_draft) {
            throw ValidationException::withMessages(['message' => trans('general.invalid_action')]);
        }
    }

    /**
     * Check if invoice is accessible to authenticated user.
     *
     * @param Invoice $invoice
     * @return string
     */
    public function accessible(Invoice $invoice)
    {
        $auth_user = \Auth::user();

        if ($auth_user->hasRole(config('system.default_role.customer')) && $invoice->customer_id === $auth_user->id && !$invoice->is_draft && !$invoice->is_cancelled) {
            return 1;
        }

        if ($auth_user->can('access-all-designation') || ($auth_user->can('access-subordinate-designation') && in_array($invoice->UserAdded->Profile->designation_id, $this->designation->getSubordinate($auth_user, 1)))) {
            return 1;
        }
        
        throw ValidationException::withMessages(['message' => trans('general.permission_denied')]);
    }

    /**
     * Paginate all invoices using given params.
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($params)
    {
        $sort_by              = isset($params['sort_by']) ? $params['sort_by'] : 'created_at';
        $order                = isset($params['order']) ? $params['order'] : 'desc';
        $page_length          = isset($params['page_length']) ? $params['page_length'] : config('config.page_length');
        $prefix               = isset($params['prefix']) ? $params['prefix'] : '';
        $number               = isset($params['number']) ? $params['number'] : '';
        $customer_id          = (\Auth::user()->hasRole(config('system.default_role.customer'))) ? \Auth::user()->id : (isset($params['customer_id']) ? $params['customer_id'] : '');
        $status               = isset($params['status']) ? $params['status'] : '';
        $recurring            = isset($params['recurring']) ? $params['recurring'] : '';
        $recurring_invoice_id = isset($params['recurring_invoice_id']) ? $params['recurring_invoice_id'] : '';
        $date_start_date      = isset($params['date_start_date']) ? $params['date_start_date'] : null;
        $date_end_date        = isset($params['date_end_date']) ? $params['date_end_date'] : null;
        $due_date_start_date  = isset($params['due_date_start_date']) ? $params['due_date_start_date'] : null;
        $due_date_end_date    = isset($params['due_date_end_date']) ? $params['due_date_end_date'] : null;

        $query = $this->invoice->with('currency', 'customer', 'customer.profile', 'customer.profile.company', 'invoiceItem', 'invoiceItem.item','invoiceItem.item.itemPrice');

        if (\Auth::user()->hasRole(config('system.default_role.customer')))
            $query->whereIsDraft(0)->whereCustomerId(\Auth::user()->id);

        return $query->filterByPrefix($prefix)->filterByNumber($number)->filterByCustomerId($customer_id)->filterByStatus($status)->filterByRecurringInvoiceId($recurring_invoice_id)->dateBetween([
            'start_date' => $date_start_date,
            'end_date' => $date_end_date
        ])->dueDateBetween([
            'start_date' => $due_date_start_date,
            'end_date' => $due_date_end_date
        ])->orderBy($sort_by, $order)->paginate($page_length);
    }

    /**
     * Store invoice
     *
     * @param array $params
     * @param string $uuid
     * @return Invoice
     */
    public function store($params = array(), $uuid = null)
    {
        $this->validateInputId($params);

        if ($uuid) {
            $invoice = $this->findByUuidOrFail($uuid);

            $this->accessible($invoice);
        } else {
            $invoice = $this->invoice;
        }

        $this->validateFields($params, $uuid ? $invoice->id : null);

        $invoice->forceFill($this->formatParams($params, $uuid, $invoice))->save();

        $subtotal = $this->updateItemPrice($invoice, $uuid, $params);

        $invoice = $this->updateSubTotal($invoice, $subtotal, $params);

        $upload_token = isset($params['upload_token']) ? $params['upload_token'] : null;

        $this->processUpload($invoice, $upload_token, $uuid);

        if (! $uuid && ! $invoice->is_draft)
            $invoice = $this->send($invoice);

        return $invoice;
    }

    /**
     * Validate input ids.
     *
     * @param array $params
     * @return null
     */

    public function validateInputId($params)
    {
        $customer_ids = $this->user->listCustomerId();
        $currency_ids = $this->currency->listId();

        $customer_id = isset($params['customer_id']) ? $params['customer_id'] : null;
        $currency_id = isset($params['currency_id']) ? $params['currency_id'] : null;

        if ($customer_id && ! in_array($customer_id, $customer_ids)) {
            throw ValidationException::withMessages(['message' => trans('user.could_not_find')]);
        }

        if (! in_array($currency_id, $currency_ids)) {
            throw ValidationException::withMessages(['message' => trans('currency.could_not_find')]);
        }
    }

    /**
     * Check before send invoice to email
     *
     * @param Invoice $invoice
     * @return void
     */
    private function beforeSend(Invoice $invoice)
    {
        if(!$invoice->is_draft || $invoice->is_cancelled) {
            throw ValidationException::withMessages(['message' => trans('general.invalid_action')]);
        }

        if(!$invoice->Customer) {
            throw ValidationException::withMessages(['message' => trans('invoice.no_customer_selected')]);
        }

        if(!$invoice->due_date || !$invoice->due_date_detail) {
            throw ValidationException::withMessages(['message' => trans('invoice.no_due_date_selected')]);
        }
    }

    /**
     * Send invoice to email
     *
     * @param Invoice $invoice
     * @return void
     */
    public function send(Invoice $invoice)
    {
        if(!$invoice->is_draft)
            return $invoice;

        $this->beforeSend($invoice);

        SendInvoiceMail::dispatch($invoice->Customer->email, [
            'slug'            => 'send-invoice'
        ], $invoice);

        $invoice->is_draft = 0;
        $invoice->save();

        return $invoice;
    }

    /**
     * Send invoice to email
     *
     * @param Invoice $invoice
     * @param array $params
     * @return void
     */
    public function sendMail(Invoice $invoice, $params = array())
    {
        SendInvoiceMail::dispatch($invoice->Customer->email, [
            'body'    => isset($params['body']) ? $params['body'] : null,
            'subject' => isset($params['subject']) ? $params['subject'] : null
        ], $invoice);
    }

    /**
     * Cancel invoice
     *
     * @param Invoice $invoice
     * @return Invoice
     */
    public function cancel(Invoice $invoice)
    {
        $paid = $this->transaction->getInvoicePaidAmount($invoice->id);

        if($paid)
            throw ValidationException::withMessages(['message' => trans('invoice.payment_already_made')]);

        $invoice->is_cancelled = 1;
        $invoice->save();

        return $invoice;
    }

    /**
     * Undo cancel invoice
     *
     * @param Invoice $invoice
     * @return Invoice
     */
    public function undoCancel(Invoice $invoice)
    {
        if(!$invoice->is_cancelled) {
            throw ValidationException::withMessages(['message' => trans('general.invalid_action')]);
        }

        $invoice->is_cancelled = 0;
        $invoice->save();

        return $invoice;
    }

    /**
     * Mark invoice as sent
     *
     * @param Invoice $invoice
     * @return Invoice
     */
    public function markAsSent(Invoice $invoice)
    {
        $this->beforeSend($invoice);

        $invoice->is_draft = 0;
        $invoice->save();

        return $invoice;
    }

    /**
     * Toggle invoice partial payment feature
     *
     * @param Invoice $invoice
     * @return Invoice
     */
    public function togglePartialPayment(Invoice $invoice)
    {
        $paid = $this->transaction->getInvoicePaidAmount($invoice->id);

        if($paid)
            throw ValidationException::withMessages(['message' => trans('invoice.payment_already_made')]);

        $invoice->partial_payment = !$invoice->partial_payment;
        $invoice->save();

        return $invoice;
    }

    /**
     * Update invoice recurring feature
     *
     * @param Invoice $invoice
     * @param array $params
     * @return Invoice
     */
    public function recurring(Invoice $invoice, $params = array())
    {
        $recurrence_start_date = isset($params['recurrence_start_date']) ? toDate($params['recurrence_start_date']) : null;
        $recurrence_end_date = isset($params['recurrence_end_date']) ? toDate($params['recurrence_end_date']) : null;
        $is_recurring = (isset($params['is_recurring']) && $params['is_recurring']) ? 1 : 0;
        $recurring_frequency = isset($params['recurring_frequency']) ? $params['recurring_frequency'] : 0;

        if ($recurrence_start_date < $invoice->date || $recurrence_end_date < $invoice->date) {
            throw ValidationException::withMessages(['recurrence_start_date' => trans('invoice.recurrence_date_cannot_less_than_invoice_date')]);
        }

        $invoice->is_recurring = $is_recurring;
        $invoice->recurrence_start_date = $is_recurring ? $recurrence_start_date : null;
        $invoice->recurrence_end_date = $is_recurring ? $recurrence_end_date : null;
        $invoice->recurring_frequency = $is_recurring ? $recurring_frequency : 0;
        $recurring_days = $recurring_frequency;
        $invoice->next_recurring_date = ($is_recurring) ? date('Y-m-d', strtotime($recurrence_start_date. ' + '.$recurring_days.' days')) : null;

        if ($invoice->next_recurring_date > $invoice->recurrence_end_date) {
            $invoice->next_recurring_date = null;
        }

        $invoice->save();

        return $invoice;
    }

    /**
     * Update invoice next recurring date
     *
     * @param Invoice $invoice
     * @return Invoice
     */
    public function updateNextRecurringDate($invoice)
    {
        $next_recurring_date = date('Y-m-d', strtotime($invoice->next_recurring_date. ' + '.$invoice->recurring_frequency.' days'));
        $invoice->next_recurring_date = ($invoice->recurrence_end_date > $next_recurring_date) ? $next_recurring_date : null;
        $invoice->save();

        return $invoice;
    }

    /**
     * Copy invoice
     *
     * @param Invoice $invoice
     * @return Invoice
     */
    public function copy(Invoice $invoice)
    {
        $new_invoice = $invoice->replicate();
        $new_invoice->uuid = Str::uuid();
        $new_invoice->number = $this->getNewInvoiceNumber();
        $new_invoice->is_draft = 1;
        $new_invoice->status = 'unpaid';
        $new_invoice->user_id = (\Auth::check()) ? \Auth::user()->id : null;
        $new_invoice->is_cancelled = 0;
        $new_invoice->is_recurring = 0;
        $new_invoice->recurrence_start_date = null;
        $new_invoice->recurrence_end_date = null;
        $new_invoice->next_recurring_date = null;
        $new_invoice->recurring_frequency = 0;
        $new_invoice->recurring_invoice_id = null;
        $new_invoice->quotation_id = null;
        $new_invoice->upload_token = Str::uuid();
        $new_invoice->save();

        foreach ($invoice->InvoiceItem as $invoice_item) {
            $new_invoice_item = $invoice_item->replicate();
            $new_invoice_item->invoice_id = $new_invoice->id;
            $new_invoice_item->uuid = Str::uuid();
            $new_invoice_item->save();
        }

        $this->upload->copy($this->module, $invoice->id, $new_invoice->upload_token, $new_invoice->id);

        return $new_invoice;
    }

    /**
     * Get invoice color based on status
     *
     * @param Invoice $invoice
     * @return string $color
     */
    public function getColor(Invoice $invoice){
        if($invoice->status === 'unpaid' || !$invoice->status)
            $color = '#C9302C';
        elseif($invoice->status === 'partially_paid')
            $color = '#EC971F';
        elseif($invoice->status === 'paid')
            $color = '#5CB85C';
        else
            $color = '#337AB7';

        return $color;
    }

    /**
     * Get invoice color based on status (For Graph use only)
     *
     * @param string $status
     * @return string $color
     */
    public function getColorByStatus($status){
        if($status == 'draft')
            return '#727B84';
        elseif($status == 'cancelled')
            return '#F62D51';
        elseif($status == 'sent')
            return '#008D23';
        elseif($status == 'paid')
            return '#88DD92';
        elseif($status == 'partially_paid')
            return '#009EFB';
        elseif($status == 'overdue')
            return '#FFD071';
        else
            return;
    }

    /**
     * Get invoice status
     *
     * @param Invoice $invoice
     * @return string $status
     */
    public function getPrintStatus(Invoice $invoice){
        if($invoice->is_cancelled)
            $status = 'cancelled';
        elseif($invoice->is_draft)
            $status = 'draft';
        elseif($invoice->due_date != -1 && $invoice->due_date_detail > date('Y-m-d') && ($invoice->status === null || $invoice->status === 'unpaid'))
            $status = 'unpaid';
        elseif($invoice->status === 'paid')
            $status = 'paid';
        else
            $status = 'overdue';

        return $status;
    }

    /**
     * Update invoice item price
     *
     * @param Invoice $invoice
     * @param array $params
     * @param string $uuid
     * @return Invoice
     */
    public function updateItemPrice(Invoice $invoice, $uuid = null, $params)
    {
        $items = isset($params['rows']) ? $params['rows'] : [];
        $item_type = isset($params['item_type']) ? $params['item_type'] : 'quantity';
        $item_uuid = array();
        foreach ($items as $item) {
            $item_uuid[] = $item['uuid'];
        }

        if ($uuid) {
            $previous_items = $this->invoice_item->filterByInvoiceId($invoice->id)->get()->pluck('uuid')->all();
            foreach ($previous_items as $previous_item) {
                if (!in_array($previous_item, $item_uuid)) {
                    $this->invoice_item->filterByUuid($previous_item)->delete();
                }
            }
        }

        $subtotal = 0;
        foreach ($items as $item) {
            $item_uuid   = isset($item['uuid']) ? $item['uuid'] : null;
            $name        = isset($item['name']) ? $item['name'] : null;
            $item_id     = isset($item['selected_item']['id']) ? $item['selected_item']['id'] : null;
            $custom_name = isset($item['custom_name']) ? $item['custom_name'] : null;
            $description = isset($item['description']) ? $item['description'] : null;
            $quantity    = (isset($item['quantity']) && ($item_type === 'quantity' || $item_type === 'hour')) ? formatNumber($item['quantity'], config('config.invoice_line_item_discount_decimal_place')) : 1;
            $unit_price  = isset($item['unit_price']) ? formatNumber($item['unit_price'], $invoice->Currency->decimal_place) : 0;
            $discount    = (isset($item['discount']) && $invoice->line_item_discount) ? formatNumber($item['discount'], config('config.invoice_line_item_discount_decimal_place')) : 0;
            $tax         = (isset($item['tax']) && $invoice->line_item_tax) ? formatNumber($item['tax'], config('config.invoice_line_item_tax_decimal_place')) : 0;

            $amount = $quantity * $unit_price;
            $amount = $amount - ($invoice->line_item_discount_type ? ($amount * $discount/100) : $discount);
            $amount = $amount + ($amount * $tax/100);
            $amount = formatNumber($amount, $invoice->Currency->decimal_place);

            $invoice_item = $this->invoice_item->firstOrCreate([
                'uuid' => $item_uuid,
                'invoice_id' => $invoice->id
            ]);

            $invoice_item->item_id = ($item_id) ? : null;
            $invoice_item->name = ($custom_name) ? : null;
            $invoice_item->quantity = $quantity;
            $invoice_item->unit_price = $unit_price;
            $invoice_item->discount = $discount;
            $invoice_item->tax = $tax;
            $invoice_item->amount = $amount;
            $invoice_item->description = $description;
            $invoice_item->save();

            $subtotal += $amount;
        }

        return $subtotal;
    }

    /**
     * Update invoice sub total
     *
     * @param Invoice $invoice
     * @param numeric $subtotal
     * @param array $params
     * @return Invoice
     */
    private function updateSubTotal($invoice, $subtotal = 0, $params = array())
    {
        $subtotal_discount = isset($params['subtotal_discount']) ? $params['subtotal_discount'] : 0;
        $subtotal_tax = isset($params['subtotal_tax']) ? $params['subtotal_tax'] : 0;
        $subtotal_shipping_and_handling = isset($params['subtotal_shipping_and_handling']) ? $params['subtotal_shipping_and_handling'] : 0;

        $subtotal_discount_amount = ($invoice->subtotal_discount) ? formatNumber($subtotal_discount, config('config.invoice_subtotal_discount_decimal_place')) : 0;
        $subtotal_tax_amount = ($invoice->subtotal_tax) ? formatNumber($subtotal_tax, config('config.invoice_subtotal_tax_decimal_place')) : 0;
        $subtotal_shipping_and_handling_amount = ($invoice->subtotal_shipping_and_handling) ? formatNumber($subtotal_shipping_and_handling, config('config.invoice_subtotal_shipping_and_handling_decimal_place')) : 0;

        $total = $subtotal;
        $total = $total - ($invoice->subtotal_discount_type ? ($total * $subtotal_discount/100) : $subtotal_discount);
        $total = $total + ($total * $subtotal_tax/100);
        $total = $total + $subtotal_shipping_and_handling;
        $total = formatNumber($total, $invoice->Currency->decimal_place);

        $invoice->subtotal_tax_amount = $subtotal_tax_amount;
        $invoice->subtotal_discount_amount = $subtotal_discount_amount;
        $invoice->subtotal_shipping_and_handling_amount = $subtotal_shipping_and_handling_amount;
        $invoice->subtotal = $subtotal;
        $invoice->total = $total;
        $invoice->save();

        return $invoice;
    }

    /**
     * Fix invoice attachments
     *
     * @param Invoice $invoice
     * @param string $upload_token
     * @param string $uuid
     * @return void
     */
    public function processUpload(Invoice $invoice, $upload_token, $uuid = null)
    {
        if ($uuid) {
            $this->upload->store($this->module, $invoice->id, $upload_token);
        } else {
            $this->upload->update($this->module, $invoice->id, $upload_token);
        }
    }

    /**
     * Prepare given params for inserting into database.
     *
     * @param array $params
     * @param string $type
     * @return array
     */
    private function formatParams($params, $uuid, $invoice)
    {
        $currency = $this->currency->findOrFail(isset($params['currency_id']) ? $params['currency_id'] : null);
        $is_draft = (isset($params['is_draft']) && $params['is_draft']) ? 1 : 0;
        $due_date = isset($params['due_date']) ? $params['due_date'] : 0;
        $date     = isset($params['date']) ? toDate($params['date']) : null;

        $formatted = [
            'is_draft'                       => (!$uuid && $is_draft) ? 1 : (($uuid && $invoice->is_draft) ? 1 : 0),
            'reference_number'               => isset($params['reference_number']) ? $params['reference_number'] : null,
            'number'                         => isset($params['invoice_number']) ? $params['invoice_number'] : null,
            'prefix'                         => isset($params['invoice_prefix']) ? $params['invoice_prefix'] : null,
            'customer_id'                    => isset($params['customer_id']) ? $params['customer_id'] ? : null : null,
            'currency_id'                    => $currency->id,
            'item_type'                      => isset($params['item_type']) ? $params['item_type'] : 'quantity',
            'date'                           => $date,
            'due_date'                       => $due_date,
            'due_date_detail'                => isset($params['due_date_detail']) ? toDate($params['due_date_detail']) : null,
            'line_item_discount_type'        => (isset($params['line_item_discount_type']) && $params['line_item_discount_type']) ? 1 : 0,
            'line_item_tax'                  => (isset($params['show_line_item_tax']) && $params['show_line_item_tax']) ? 1 : 0,
            'line_item_discount'             => (isset($params['show_line_item_discount']) && $params['show_line_item_discount']) ? 1 : 0,
            'line_item_description'          => (isset($params['show_line_item_description']) && $params['show_line_item_description']) ? 1 : 0,
            'subtotal_tax'                   => (isset($params['show_subtotal_tax']) && $params['show_subtotal_tax']) ? 1 : 0,
            'subtotal_discount'              => (isset($params['show_subtotal_discount']) && $params['show_subtotal_discount']) ? 1 : 0,
            'subtotal_shipping_and_handling' => (isset($params['show_subtotal_shipping_and_handling']) && $params['show_subtotal_shipping_and_handling']) ? 1 : 0,
            'subtotal_discount_type'         => (isset($params['subtotal_discount_type']) && $params['subtotal_discount_type']) ? 1 : 0,
            'customer_note'                  => isset($params['customer_note']) ? $params['customer_note'] : null,
            'memo'                           => isset($params['memo']) ? $params['memo'] : null,
            'tnc'                            => isset($params['tnc']) ? $params['tnc'] : null,
        ];

        if (! $due_date || $due_date === 'no_due_date') {
            $formatted['due_date_detail'] = null;
        } elseif ($due_date === 'due_on_date') {
            $formatted['due_date_detail'] = $due_date_detail;
        } else {
            $formatted['due_date_detail'] = date('Y-m-d', strtotime($date . ' +'.$due_date.' day'));
        }

        if (! $uuid) {
            $formatted['user_id'] = \Auth::user()->id;
            $formatted['uuid'] = Str::uuid();
            $formatted['upload_token'] = isset($params['upload_token']) ? $params['upload_token'] : null;
            $formatted['partial_payment'] = config('config.invoice_partial_payment') ? : 0;
        }

        return $formatted;
    }

    /**
     * Validate invoice fields
     *
     * @param array $params
     * @param integer $id (optional)
     * @return void
     */
    private function validateFields($params = array(), $id = null)
    {
        $this->validateInvoiceNumber($params, $id);

        $this->validateDates($params);

        $this->validateItems($params);

        $this->validateSubTotal($params);

        $this->validatePreviousTransaction($params, $id);
    }

    /**
     * Validate invoice number
     *
     * @param array $params
     * @return void
     */
    private function validateInvoiceNumber($params, $id = null)
    {
        $invoice_number = isset($params['invoice_number']) ? $params['invoice_number'] : null;

        $invoice = ($id) ? $this->findOrFail($id) : null;

        $query = $this->invoice->whereNotNull('id');

        if ($invoice) {
            if ($invoice->number == $invoice_number) {
                return;
            }

            $query->where('id','!=',$id);
        }

        if ($query->where(function($q) use($invoice_number) {
                $q->where('number', '>', $invoice_number)->orWhere('number','=',$invoice_number);
            })->count()) {
            throw ValidationException::withMessages(['invoice_number' => trans('invoice.invalid_invoice_number')]);
        }
    }

    /**
     * Validate invoice date
     *
     * @param array $params
     * @return void
     */
    private function validateDates($params)
    {
        $date = isset($params['date']) ? $params['date'] : null;
        $due_date = isset($params['due_date']) ? $params['due_date'] : null;
        $due_date_detail = isset($params['due_date_detail']) ? $params['due_date_detail'] : null;

        if ($due_date === 'due_on_date' && (! $due_date_detail || $date > $due_date_detail)) {
            throw ValidationException::withMessages(['due_date_detail' => trans('invoice.valid_due_date_detail_required')]);
        }
    }

    /**
     * Validate invoice items
     *
     * @param array $params
     * @return void
     */
    private function validateItems($params)
    {
        $items = isset($params['rows']) ? $params['rows'] : null;
        $item_type = isset($params['item_type']) ? $params['item_type'] : 'quantity';

        $line_item_tax = (isset($params['show_line_item_tax']) && $params['show_line_item_tax']) ? 1 : 0;
        $line_item_discount = (isset($params['show_line_item_discount']) && $params['show_line_item_discount']) ? 1 : 0;
        $line_item_description = (isset($params['show_line_item_description']) && $params['show_line_item_description']) ? 1 : 0;

        if (! $items) {
            throw ValidationException::withMessages(['message' => trans('invoice.no_item_found')]);
        }

        $errors = array();

        foreach ($items as $key => $item) {
            $name = isset($item['name']) ? $item['name'] : null;
            $custom_name = isset($item['custom_name']) ? $item['custom_name'] : null;
            $quantity = isset($item['quantity']) ? $item['quantity'] : 0;
            $unit_price = isset($item['unit_price']) ? $item['unit_price'] : 0;
            $discount = isset($item['discount']) ? $item['discount'] : 0;
            $tax = isset($item['tax']) ? $item['tax'] : 0;

            if (! $name && ! $custom_name) {
                $errors['item_name_'.$key] = [trans('invoice.item_error')];
            }

            if (($item_type === 'quantity' || $item_type === 'hour') && (! is_numeric($quantity) || $quantity < 0)) {
                $errors['item_quantity_'.$key] = [trans('invoice.quantity_error')];
            }

            if (! is_numeric($unit_price) || $unit_price < 0) {
                $errors['item_unit_price_'.$key] = [trans('invoice.unit_price_error')];
            }

            if ($line_item_discount && (! is_numeric($discount) || $discount < 0)) {
                $errors['item_discount_'.$key] = [trans('invoice.discount_error')];
            }

            if ($line_item_tax && (! is_numeric($tax) || $tax < 0)) {
                $errors['item_tax_'.$key] = [trans('invoice.tax_error')];
            }
        }

        if (count($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Validate invoice sub total
     *
     * @param array $params
     * @return void
     */
    private function validateSubTotal($params)
    {
        $subtotal_tax = (isset($params['show_subtotal_tax']) && $params['show_subtotal_tax']) ? 1 : 0;
        $subtotal_discount = (isset($params['show_subtotal_discount']) && $params['show_subtotal_discount']) ? 1 : 0;
        $subtotal_shipping_and_handling = (isset($params['show_subtotal_shipping_and_handling']) && $params['show_subtotal_shipping_and_handling']) ? 1 : 0;

        $subtotal_discount = isset($params['subtotal_discount']) ? $params['subtotal_discount'] : 0;
        $subtotal_tax = isset($params['subtotal_tax']) ? $params['subtotal_tax'] : 0;
        $subtotal_shipping_and_handling = isset($params['subtotal_shipping_and_handling']) ? $params['subtotal_shipping_and_handling'] : 0;
        $total = isset($params['total']) ? $params['total'] : 0;

        $errors = array();

        if ($subtotal_discount && (!is_numeric($subtotal_discount) || $subtotal_discount < 0)) {
            $errors['subtotal_discount'] = [trans('invoice.discount_error')];
        }

        if ($subtotal_tax && (!is_numeric($subtotal_tax) || $subtotal_tax < 0)) {
            $errors['subtotal_tax'] = [trans('invoice.tax_error')];
        }

        if ($subtotal_shipping_and_handling && (!is_numeric($subtotal_shipping_and_handling) || $subtotal_shipping_and_handling < 0)) {
            $errors['subtotal_shipping_and_handling'] = [trans('invoice.shipping_and_handling_error')];
        }

        if (!is_numeric($total) || $total < 0) {
            $errors['message'] = [trans('invoice.total_error')];
        }

        if (count($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Validate previous invoice transactions
     *
     * @param array $params
     * @param integer $id (optional)
     * @return void
     */
    private function validatePreviousTransaction($params, $id = null)
    {
        if (! $id) {
            return;
        }

        $invoice = $this->findOrFail($id);

        $currency_id = isset($params['currency_id']) ? $params['currency_id'] : null;
        $date = isset($params['date']) ? $params['date'] : null;
        $total = isset($params['total']) ? $params['total'] : 0;

        $paid = $this->transaction->getInvoicePaidAmount($id);
        $last_payment_date = ($paid) ? $this->transaction->getInvoiceLastPaymentDate($id) : null;

        if ($paid && $currency_id != $invoice->currency_id) {
            throw ValidationException::withMessages(['message' => trans('invoice.payment_already_made')]);
        }

        if ($paid && $date > $last_payment_date) {
            throw ValidationException::withMessages(['date' => trans('invoice.payment_date_less_than_invoice_date')]);
        }

        if ($paid > $total) {
            throw ValidationException::withMessages(['message' => trans('invoice.payment_cannot_be_more_than_total')]);
        }
    }

    /**
     * Check if invoice is deletable.
     *
     * @param string $uuid
     * @return bool|null
     */
    public function deletable($uuid)
    {
        $invoice = $this->findByUuidOrFail($uuid);

        $paid = $this->transaction->getInvoicePaidAmount($invoice->id);

        if($paid)
            throw ValidationException::withMessages(['message' => trans('invoice.payment_already_made')]);

        return $invoice;
    }

    /**
     * Delete invoice.
     *
     * @param integer $id
     * @return bool|null
     */
    public function delete(Invoice $invoice)
    {
        return $invoice->delete();
    }

    /**
     * Delete multiple invoices.
     *
     * @param array $ids
     * @return bool|null
     */
    public function deleteMultiple($ids)
    {
        return $this->invoice->whereIn('id', $ids)->delete();
    }

    /**
     * Validation before payment.
     *
     * @param Invoice $invoice
     * @param array $params
     * @return void
     */
    public function beforePayment(Invoice $invoice, $params = array())
    {
        $default_currency = $this->currency->default();

        $conversion_rate = isset($params['conversion_rate']) ? $params['conversion_rate'] : 0;
        $amount = isset($params['amount']) ? $params['amount'] : 0;
        $date = isset($params['date']) ? $params['date'] : null;

        if ($invoice->currency_id != $default_currency->id && ! $conversion_rate) {
            throw ValidationException::withMessages(['conversion_rate' => trans('validation.required', ['attribute' => trans('transaction.conversion_rate')])]);
        }

        $paid = $this->transaction->getInvoicePaidAmount($invoice->id);
        $balance = $invoice->total - $paid;

        if ($amount > $balance) {
            throw ValidationException::withMessages(['message' => trans('invoice.balance_less_than_payment_amount')]);
        }

        if ($date < $invoice->date) {
            throw ValidationException::withMessages(['message' => trans('invoice.payment_date_less_than_invoice_date')]);
        }

        if (!$invoice->partial_payment && $amount != $invoice->total) {
            throw ValidationException::withMessages(['message' => trans('invoice.partial_payment_disabled')]);
        }
    }

    /**
     * Process invoice payment.
     *
     * @param Invoice $invoice
     * @param array $params
     * @return Invoice
     */
    public function payment(Invoice $invoice, $params = array())
    {
        $this->beforePayment($invoice, $params);

        $transaction = $this->transaction->create([
            'type'                    => 'income',
            'transaction_category_id' => isset($params['income_category_id']) ? $params['income_category_id'] : null,
            'account_id'              => isset($params['account_id']) ? $params['account_id'] : null,
            'customer_id'             => $invoice->customer_id,
            'currency_id'             => $invoice->currency_id,
            'conversion_rate'         => isset($params['conversion_rate']) ? $params['conversion_rate'] : 1,
            'amount'                  => isset($params['amount']) ? $params['amount'] : 0,
            'date'                    => isset($params['date']) ? toDate($params['date']) : null,
            'payment_method_id'       => isset($params['payment_method_id']) ? $params['payment_method_id'] : null,
            'reference_number'        => $invoice->reference_number,
            'description'             => isset($params['description']) ? $params['description'] : null,
            'invoice_id'              => $invoice->id,
            'upload_token'            => isset($params['upload_token']) ? $params['upload_token'] : null
        ]);

        if (isset($params['email']) && $params['email']) {
            SendInvoiceMail::dispatch($invoice->Customer->email, [
                'slug'            => 'invoice-payment-confirmation'
            ], $invoice, $transaction);
        }

        $this->updateStatus($invoice);

        return $transaction;
    }

    /**
     * Update invoice status.
     *
     * @param Invoice $invoice
     * @return Invoice
     */
    public function updateStatus(Invoice $invoice)
    {
        $paid = $this->transaction->getInvoicePaidAmount($invoice->id);

        if(!$paid)
            $invoice->status = 'unpaid';
        elseif($paid < $invoice->total)
            $invoice->status = 'partially_paid';
        else
            $invoice->status = 'paid';

        $invoice->save();

        return $invoice;
    }

    /**
     * Get graph data by status
     *
     * @return array
     */
    public function graphByStatus()
    {
        $status = array();
        foreach($this->invoice->select('status','is_draft','is_cancelled','due_date_detail')->get() as $invoice){
            if($invoice->is_draft)
                array_push($status,'draft');
            elseif($invoice->is_cancelled)
                array_push($status,'cancelled');
            elseif($invoice->status == 'sent' || $invoice->status == 'unpaid' || $invoice->status == null)
                array_push($status,'sent');
            elseif($invoice->status == 'paid')
                array_push($status,'paid');
            elseif($invoice->status == 'partially_paid' && $invoice->due_date_detail > date('Y-m-d'))
                array_push($status,'partially_paid');
            elseif($invoice->status != 'paid' && $invoice->due_date_detail < date('Y-m-d'))
                array_push($status,'overdue');
        }

        $invoice_status = array();
        foreach(array_count_values($status) as $key => $value)
            $invoice_status[] = array('name' => trans('invoice.status_'.$key),'total' => $value,'color' => $this->getColorByStatus($key));

        return $invoice_status;
    }
}
