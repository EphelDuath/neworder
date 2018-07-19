<?php
namespace App\Repositories;

use App\Invoice;
use App\Quotation;
use App\InvoiceItem;
use App\Jobs\SendMail;
use App\QuotationItem;
use Illuminate\Support\Str;
use App\QuotationDiscussion;
use App\Jobs\SendQuotationMail;
use App\Repositories\UserRepository;
use App\Repositories\UploadRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\CurrencyRepository;
use App\Repositories\EmailLogRepository;
use App\Repositories\DesignationRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Validation\ValidationException;

class QuotationRepository
{
    protected $quotation;
    protected $designation;
    protected $currency;
    protected $upload;
    protected $quotation_item;
    protected $email;
	protected $quotation_discussion;
	protected $invoice;
	protected $invoiceRepo;
	protected $invoice_item;
    protected $user;
    protected $module = 'quotation';

    /**
     * Instantiate a new instance.
     *
     * @return void
     */
    public function __construct(
        Quotation $quotation,
        DesignationRepository $designation,
        CurrencyRepository $currency,
        UploadRepository $upload,
        QuotationItem $quotation_item,
        EmailLogRepository $email,
		QuotationDiscussion $quotation_discussion,
		Invoice $invoice,
		InvoiceRepository $invoiceRepo,
		InvoiceItem $invoice_item,
        UserRepository $user
    ) {
        $this->quotation = $quotation;
        $this->designation = $designation;
        $this->currency = $currency;
        $this->upload = $upload;
        $this->quotation_item = $quotation_item;
        $this->email = $email;
		$this->quotation_discussion = $quotation_discussion;
		$this->invoice = $invoice;
		$this->invoiceRepo = $invoiceRepo;
		$this->invoice_item = $invoice_item;
        $this->user = $user;
    }

    /**
     * Get quotation query
     *
     * @return Quotation query
     */
    public function getQuery()
    {
        return $this->quotation->with('currency', 'customer', 'customer.profile', 'customer.profile.company', 'quotationItem', 'quotationItem.item','quotationItem.item.itemPrice');
    }

    /**
     * Count quotation
     *
     * @return integer
     */
    public function count()
    {
        return $this->quotation->count();
    }

    /**
     * List all quotations by number & id
     *
     * @return array
     */
    public function listAll()
    {
        return $this->quotation->all()->pluck('number', 'id')->all();
    }

    /**
     * Get all quotations
     *
     * @return array
     */
    public function getAll()
    {
        return $this->quotation->with('currency', 'customer', 'customer.profile', 'customer.profile.company', 'quotationItem', 'quotationItem.item','quotationItem.item.itemPrice')->all();
    }

    /**
     * Find quotation with given id or throw an error.
     *
     * @param integer $id
     * @return Quotation
     */
    public function findOrFail($id)
    {
        $quotation = $this->quotation->with('currency', 'customer', 'customer.profile', 'customer.profile.company', 'quotationItem', 'quotationItem.item','quotationItem.item.itemPrice')->find($id);

        if (! $quotation) {
            throw ValidationException::withMessages(['message' => trans('quotation.could_not_find')]);
        }

        return $quotation;
    }

    /**
     * Find quotation with given uuid or throw an error.
     *
     * @param string $uuid
     * @return Quotation
     */
    public function findByUuidOrFail($uuid)
    {
        $quotation = $this->quotation->with('currency', 'customer', 'customer.profile', 'customer.profile.company', 'quotationItem', 'quotationItem.item','quotationItem.item.itemPrice')->whereUuid($uuid)->first();

        if (! $quotation) {
            throw ValidationException::withMessages(['message' => trans('quotation.could_not_find')]);
        }

        return $quotation;
    }

    /**
     * List all expiry dates for quotation.
     *
     * @return array
     */
    public function listExpiryDate()
    {
        return [
            ['id' => '-1', 'name' => trans('quotation.no_expiry_date')],
            ['id' => '0', 'name' => trans('quotation.expiry_on_quotation_date')],
            ['id' => 'expiry_on_date', 'name' => trans('quotation.expiry_on_date')],
            ['id' => '2', 'name' => trans('quotation.expiry_in_days',['day' => 2])],
            ['id' => '5', 'name' => trans('quotation.expiry_in_days',['day' => 5])],
            ['id' => '7', 'name' => trans('quotation.expiry_in_days',['day' => 7])],
            ['id' => '10', 'name' => trans('quotation.expiry_in_days',['day' => 10])],
            ['id' => '15', 'name' => trans('quotation.expiry_in_days',['day' => 15])],
            ['id' => '30', 'name' => trans('quotation.expiry_in_days',['day' => 30])],
            ['id' => '45', 'name' => trans('quotation.expiry_in_days',['day' => 45])],
            ['id' => '90', 'name' => trans('quotation.expiry_in_days',['day' => 90])],
        ];
    }

    /**
     * Get new quotation number.
     *
     * @return string
     */
    public function getNewQuotationNumber()
    {
        $current_number = ($this->quotation->max('number')) ? : 0;
        $number = (($current_number >= config('config.quotation_start_number')) ? $current_number + 1 : config('config.quotation_start_number'));
        return str_pad($number, config('config.quotation_number_digit'), '0', STR_PAD_LEFT);
    }

    /**
     * Check if quotation is cancelled.
     *
     * @param Quotation $quotation
     * @return string
     */
    public function isCancelled(Quotation $quotation)
    {
        if ($quotation->is_cancelled) {
            throw ValidationException::withMessages(['message' => trans('quotation.quotation_already_cancelled')]);
        }
    }

    /**
     * Check if quotation is invoiced.
     *
     * @param Quotation $quotation
     * @return string
     */
    public function isInvoiced(Quotation $quotation)
    {
        if ($quotation->status === 'invoiced') {
            throw ValidationException::withMessages(['message' => trans('general.invalid_action')]);
        }
    }

    /**
     * Check if quotation is draft.
     *
     * @param Quotation $quotation
     * @return string
     */
    public function isDraft(Quotation $quotation)
    {
        if ($quotation->is_draft) {
            throw ValidationException::withMessages(['message' => trans('general.invalid_action')]);
        }
    }

    /**
     * Check if quotation is accessible to authenticated user.
     *
     * @param Quotation $quotation
     * @return string
     */
    public function accessible(Quotation $quotation)
    {
        $auth_user = \Auth::user();

        if ($auth_user->hasRole(config('system.default_role.customer')) && $quotation->customer_id === $auth_user->id && !$quotation->is_draft && !$quotation->is_cancelled) {
            return 1;
        }

        if ($auth_user->can('access-all-designation') || ($auth_user->can('access-subordinate-designation') && in_array($quotation->UserAdded->Profile->designation_id, $this->designation->getSubordinate($auth_user, 1)))) {
            return 1;
        }
        
        throw ValidationException::withMessages(['message' => trans('general.permission_denied')]);
    }

    /**
     * Paginate all quotations using given params.
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($params)
    {
        $sort_by                = isset($params['sort_by']) ? $params['sort_by'] : 'created_at';
        $order                  = isset($params['order']) ? $params['order'] : 'desc';
        $page_length            = isset($params['page_length']) ? $params['page_length'] : config('config.page_length');
        $prefix                 = isset($params['prefix']) ? $params['prefix'] : '';
        $number                 = isset($params['number']) ? $params['number'] : '';
        $customer_id            = (\Auth::user()->hasRole(config('system.default_role.customer'))) ? \Auth::user()->id : (isset($params['customer_id']) ? $params['customer_id'] : '');
        $status                 = isset($params['status']) ? $params['status'] : '';
        $date_start_date        = isset($params['date_start_date']) ? $params['date_start_date'] : null;
        $date_end_date          = isset($params['date_end_date']) ? $params['date_end_date'] : null;
        $expiry_date_start_date = isset($params['expiry_date_start_date']) ? $params['expiry_date_start_date'] : null;
        $expiry_date_end_date   = isset($params['expiry_date_end_date']) ? $params['expiry_date_end_date'] : null;

        return $this->quotation->with('currency', 'customer', 'customer.profile', 'customer.profile.company', 'quotationItem', 'quotationItem.item','quotationItem.item.itemPrice')->filterByPrefix($prefix)->filterByNumber($number)->filterByCustomerId($customer_id)->filterByStatus($status)->dateBetween([
            'start_date' => $date_start_date,
            'end_date' => $date_end_date
        ])->expiryDateBetween([
            'start_date' => $expiry_date_start_date,
            'end_date' => $expiry_date_end_date
        ])->orderBy($sort_by, $order)->paginate($page_length);
    }

    /**
     * Store quotation
     *
     * @param array $params
     * @param string $uuid
     * @return Quotation
     */
    public function store($params = array(), $uuid = null)
    {
        $this->validateInputId($params);

        if ($uuid) {
            $quotation = $this->findByUuidOrFail($uuid);

            $this->accessible($quotation);
        } else {
            $quotation = $this->quotation;
        }

        $this->validateFields($params, $uuid ? $quotation->id : null);

        $quotation->forceFill($this->formatParams($params, $uuid, $quotation))->save();

        $subtotal = $this->updateItemPrice($quotation, $uuid, $params);

        $quotation = $this->updateSubTotal($quotation, $subtotal, $params);

        $upload_token = isset($params['upload_token']) ? $params['upload_token'] : null;

        $this->processUpload($quotation, $upload_token, $uuid);

        if (! $uuid && ! $quotation->is_draft)
            $quotation = $this->send($quotation);

        return $quotation;
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
     * Check before send quotation to email
     *
     * @param Quotation $quotation
     * @return void
     */
    private function beforeSend(Quotation $quotation)
    {
        if(!$quotation->is_draft || $quotation->is_cancelled) {
            throw ValidationException::withMessages(['message' => trans('general.invalid_action')]);
        }

        if(!$quotation->subject || !$quotation->description) {
            throw ValidationException::withMessages(['message' => trans('quotation.missing_subject_description')]);
        }

        if(!$quotation->Customer) {
            throw ValidationException::withMessages(['message' => trans('quotation.no_customer_selected')]);
        }

        if(!$quotation->expiry_date || !$quotation->expiry_date_detail) {
            throw ValidationException::withMessages(['message' => trans('quotation.no_expiry_date_selected')]);
        }
    }

    /**
     * Send quotation to email
     *
     * @param Quotation $quotation
     * @return void
     */
    public function send(Quotation $quotation)
    {
        if(!$quotation->is_draft)
            return $quotation;

        $this->beforeSend($quotation);

        SendQuotationMail::dispatch($quotation->Customer->email, [
            'slug' => 'send-quotation'
        ], $quotation);

        $quotation->is_draft = 0;
        $quotation->save();

        return $quotation;
    }

    /**
     * Send quotation to email
     *
     * @param Quotation $quotation
     * @param array $params
     * @return void
     */
    public function sendMail(Quotation $quotation, $params = array())
    {
        SendQuotationMail::dispatch($quotation->Customer->email, [
            'body'    => isset($params['body']) ? $params['body'] : null,
            'subject' => isset($params['subject']) ? $params['subject'] : null
        ], $quotation);
    }

    /**
     * Cancel quotation
     *
     * @param Quotation $quotation
     * @return Quotation
     */
    public function cancel(Quotation $quotation)
    {
        $quotation->is_cancelled = 1;
        $quotation->save();

        return $quotation;
    }

    /**
     * Undo cancel quotation
     *
     * @param Quotation $quotation
     * @return Quotation
     */
    public function undoCancel(Quotation $quotation)
    {
        if(!$quotation->is_cancelled) {
            throw ValidationException::withMessages(['message' => trans('general.invalid_action')]);
        }

        $quotation->is_cancelled = 0;
        $quotation->save();

        return $quotation;
    }

    /**
     * Mark quotation as sent
     *
     * @param Quotation $quotation
     * @return Quotation
     */
    public function markAsSent(Quotation $quotation)
    {
        $this->beforeSend($quotation);

        $quotation->is_draft = 0;
        $quotation->save();

        return $quotation;
    }

    /**
     * Copy quotation
     *
     * @param Quotation $quotation
     * @return Quotation
     */
    public function copy(Quotation $quotation)
    {
        $new_quotation = $quotation->replicate();
        $new_quotation->uuid = Str::uuid();
        $new_quotation->number = $this->getNewQuotationNumber();
        $new_quotation->is_draft = 1;
        $new_quotation->status = null;
        $new_quotation->user_id = (\Auth::check()) ? \Auth::user()->id : null;
        $new_quotation->is_cancelled = 0;
        $new_quotation->upload_token = Str::uuid();
        $new_quotation->save();

        foreach ($quotation->QuotationItem as $quotation_item) {
            $new_quotation_item = $quotation_item->replicate();
            $new_quotation_item->quotation_id = $new_quotation->id;
            $new_quotation_item->uuid = Str::uuid();
            $new_quotation_item->save();
        }

        $this->upload->copy($this->module, $quotation->id, $new_quotation->upload_token, $new_quotation->id);

        return $new_quotation;
    }

    /**
     * Process customer action to the quotation
     *
     * @param Quotation $quotation
     * @param array $params
     * @return Quotation
     */
    public function customerAction(Quotation $quotation, $params = array())
    {
    	$action = isset($params['action']) ? $params['action'] : null;

        if(!in_array($action,['accept','reject']))
            throw ValidationException::withMessages(['message' => trans('general.invalid_link')]);

        if($quotation->status != 'accepted' && $quotation->status != 'rejected' && $quotation->expiry_date != -1 && $quotation->expiry_date_detail < date('Y-m-d'))
            throw ValidationException::withMessages(['message' => trans('quotation.quotation_already_expired')]);

        if($quotation->status === 'invoiced') {
            throw ValidationException::withMessages(['message' => trans('quotation.quotation_already_invoiced')]);
        }

        if(($action === 'accept' && $quotation->status === 'accepted') || ($action === 'reject' && $quotation->status === 'rejected'))
            throw ValidationException::withMessages(['message' => trans('general.permission_denied')]);

        $quotation->status = ($action === 'accept') ? 'accepted' : 'rejected';
        $quotation->save();

        SendQuotationMail::dispatch($quotation->Customer->email,[
        	'slug' => ($action === 'accept') ? 'quotation-accepted' : 'quotation-rejected'
        ], $quotation);

        return $quotation;
    }

    /**
     * Convert quotation into invoice
     *
     * @param Quotation $quotation
     * @return Invoice
     */
    public function convertToInvoice(Quotation $quotation)
    {
    	$invoice = $this->invoice->forceCreate([
    		'uuid' => Str::uuid(),
    		'customer_id' => $quotation->customer_id,
    		'user_id' => \Auth::user()->id,
    		'currency_id' => $quotation->currency_id,
    		'prefix' => config('config.invoice_prefix'),
    		'number' => $this->invoiceRepo->getNewInvoiceNumber(),
    		'date' => date('Y-m-d'),
    		'reference_number' => $quotation->reference_number,
    		'is_draft' => 1,
    		'due_date' => -1,
	        'line_item_tax' => $quotation->line_item_tax,
	        'line_item_discount_type' => $quotation->line_item_discount_type,
	        'line_item_discount' => $quotation->line_item_discount,
	        'line_item_description' => $quotation->line_item_description,
	        'subtotal_tax' => $quotation->subtotal_tax,
	        'subtotal_discount' => $quotation->subtotal_discount,
	        'subtotal_shipping_and_handling' => $quotation->subtotal_shipping_and_handling,
	        'item_type' => $quotation->item_type,
	        'subtotal_tax_amount' => $quotation->subtotal_tax_amount,
	        'subtotal_discount_amount' => $quotation->subtotal_discount_amount,
	        'subtotal_discount_type' => $quotation->subtotal_discount_type,
	        'subtotal_shipping_and_handling_amount' => $quotation->subtotal_shipping_and_handling_amount,
	        'subtotal' => $quotation->subtotal,
	        'total' => $quotation->total,
	        'quotation_id' => $quotation->id,
	        'upload_token' => Str::uuid()
    	]);

        $quotation->status = 'invoiced';
        $quotation->invoice_id = $invoice->id;
        $quotation->save();

        foreach($quotation->QuotationItem as $quotation_item){
            $invoice_item = $this->invoice_item->forceCreate([
	            'uuid' => Str::uuid(),
	            'invoice_id' => $invoice->id,
	            'item_id' => $quotation_item->item_id,
	            'name' => $quotation_item->name,
	            'quantity' => $quotation_item->quantity,
	            'unit_price' => $quotation_item->unit_price,
	            'discount' => $quotation_item->discount,
	            'discount_type' => $quotation_item->discount_type,
	            'tax' => $quotation_item->tax,
	            'amount' => $quotation_item->amount,
	            'description' => $quotation_item->description,
            ]);
        }

        return $invoice;
    }

    /**
     * Get quotation color based on status
     *
     * @param Quotation $quotation
     * @return string $color
     */
    public function getColor(Quotation $quotation){
        if($quotation->is_draft)
            $color = '#337AB7';
        elseif($quotation->status === 'sent' || !$quotation->status)
            $color = '#C9302C';
        elseif($quotation->status === 'rejected' || $quotation->status === 'expired')
            $color = '#FF0000';
        elseif($quotation->status === 'accepted' || $quotation->status === 'invoiced')
            $color = '#5CB85C';
        else
            $color = '#C9302C';

        return $color;
    }

    /**
     * Get quotation color based on status (For Graph use only)
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
        elseif($status == 'accepted')
            return '#88DD92';
        elseif($status == 'invoiced')
            return '#32CD32';
        elseif($status == 'rejected')
            return '#FFD071';
        elseif($status == 'expired')
            return '#ff0000';
        else
            return;
    }

    /**
     * Get quotation status
     *
     * @param Quotation $quotation
     * @return string $status
     */
    public function getPrintStatus(Quotation $quotation){
        if($quotation->is_cancelled)
            $status = 'cancelled';
        elseif($quotation->is_draft)
            $status = 'draft';
        elseif($quotation->status === 'accepted')
            $status = 'accepted';
        elseif($quotation->status === 'rejected')
            $status = 'rejected';
        elseif($quotation->status === 'invoiced')
            $status = 'invoiced';
        elseif($quotation->expiry_date != -1 && $quotation->expiry_date_detail > date('Y-m-d') && ($quotation->status === null || $quotation->status === 'sent'))
            $status = 'sent';
        else
            $status = 'expired';

        return $status;
    }

    /**
     * Update quotation item price
     *
     * @param Quotation $quotation
     * @param array $params
     * @param string $uuid
     * @return Quotation
     */
    public function updateItemPrice(Quotation $quotation, $uuid = null, $params)
    {
        $items = isset($params['rows']) ? $params['rows'] : [];
        $item_type = isset($params['item_type']) ? $params['item_type'] : 'quantity';
        $item_uuid = array();
        foreach ($items as $item) {
            $item_uuid[] = $item['uuid'];
        }

        if ($uuid) {
            $previous_items = $this->quotation_item->filterByQuotationId($quotation->id)->get()->pluck('uuid')->all();
            foreach ($previous_items as $previous_item) {
                if (!in_array($previous_item, $item_uuid)) {
                    $this->quotation_item->filterByUuid($previous_item)->delete();
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
            $quantity    = (isset($item['quantity']) && ($item_type === 'quantity' || $item_type === 'hour')) ? formatNumber($item['quantity'], config('config.quotation_line_item_discount_decimal_place')) : 1;
            $unit_price  = isset($item['unit_price']) ? formatNumber($item['unit_price'], $quotation->Currency->decimal_place) : 0;
            $discount    = (isset($item['discount']) && $quotation->line_item_discount) ? formatNumber($item['discount'], config('config.quotation_line_item_discount_decimal_place')) : 0;
            $tax         = (isset($item['tax']) && $quotation->line_item_tax) ? formatNumber($item['tax'], config('config.quotation_line_item_tax_decimal_place')) : 0;

            $amount = $quantity * $unit_price;
            $amount = $amount - ($quotation->line_item_discount_type ? ($amount * $discount/100) : $discount);
            $amount = $amount + ($amount * $tax/100);
            $amount = formatNumber($amount, $quotation->Currency->decimal_place);

            $quotation_item = $this->quotation_item->firstOrCreate([
                'uuid' => $item_uuid,
                'quotation_id' => $quotation->id
            ]);

            $quotation_item->item_id = ($item_id) ? : null;
            $quotation_item->name = ($custom_name) ? : null;
            $quotation_item->quantity = $quantity;
            $quotation_item->unit_price = $unit_price;
            $quotation_item->discount = $discount;
            $quotation_item->tax = $tax;
            $quotation_item->amount = $amount;
            $quotation_item->description = $description;
            $quotation_item->save();

            $subtotal += $amount;
        }

        return $subtotal;
    }

    /**
     * Update quotation sub total
     *
     * @param Quotation $quotation
     * @param numeric $subtotal
     * @param array $params
     * @return Quotation
     */
    private function updateSubTotal($quotation, $subtotal = 0, $params = array())
    {
        $subtotal_discount = isset($params['subtotal_discount']) ? $params['subtotal_discount'] : 0;
        $subtotal_tax = isset($params['subtotal_tax']) ? $params['subtotal_tax'] : 0;
        $subtotal_shipping_and_handling = isset($params['subtotal_shipping_and_handling']) ? $params['subtotal_shipping_and_handling'] : 0;

        $subtotal_discount_amount = ($quotation->subtotal_discount) ? formatNumber($subtotal_discount, config('config.quotation_subtotal_discount_decimal_place')) : 0;
        $subtotal_tax_amount = ($quotation->subtotal_tax) ? formatNumber($subtotal_tax, config('config.quotation_subtotal_tax_decimal_place')) : 0;
        $subtotal_shipping_and_handling_amount = ($quotation->subtotal_shipping_and_handling) ? formatNumber($subtotal_shipping_and_handling, config('config.quotation_subtotal_shipping_and_handling_decimal_place')) : 0;

        $total = $subtotal;
        $total = $total - ($quotation->subtotal_discount_type ? ($total * $subtotal_discount/100) : $subtotal_discount);
        $total = $total + ($total * $subtotal_tax/100);
        $total = $total + $subtotal_shipping_and_handling;
        $total = formatNumber($total, $quotation->Currency->decimal_place);

        $quotation->subtotal_tax_amount = $subtotal_tax_amount;
        $quotation->subtotal_discount_amount = $subtotal_discount_amount;
        $quotation->subtotal_shipping_and_handling_amount = $subtotal_shipping_and_handling_amount;
        $quotation->subtotal = $subtotal;
        $quotation->total = $total;
        $quotation->save();

        return $quotation;
    }

    /**
     * Fix quotation attachments
     *
     * @param Quotation $quotation
     * @param string $upload_token
     * @param string $uuid
     * @return void
     */
    public function processUpload(Quotation $quotation, $upload_token, $uuid = null)
    {
        if ($uuid) {
            $this->upload->store($this->module, $quotation->id, $upload_token);
        } else {
            $this->upload->update($this->module, $quotation->id, $upload_token);
        }
    }

    /**
     * Prepare given params for inserting into database.
     *
     * @param array $params
     * @param string $type
     * @return array
     */
    private function formatParams($params, $uuid, $quotation)
    {
		$currency    = $this->currency->findOrFail(isset($params['currency_id']) ? $params['currency_id'] : null);
		$is_draft    = (isset($params['is_draft']) && $params['is_draft']) ? 1 : 0;
		$expiry_date = isset($params['expiry_date']) ? $params['expiry_date'] : 0;
		$date        = isset($params['date']) ? toDate($params['date']) : null;

        $formatted = [
			'is_draft'                       => (!$uuid && $is_draft) ? 1 : (($uuid && $quotation->is_draft) ? 1 : 0),
			'subject'                        => isset($params['subject']) ? $params['subject'] : null,
			'description'                    => isset($params['description']) ? $params['description'] : null,
			'reference_number'               => isset($params['reference_number']) ? $params['reference_number'] : null,
			'number'                         => isset($params['quotation_number']) ? $params['quotation_number'] : null,
			'prefix'                         => isset($params['quotation_prefix']) ? $params['quotation_prefix'] : null,
			'customer_id'                    => isset($params['customer_id']) ? $params['customer_id'] ? : null : null,
			'currency_id'                    => $currency->id,
			'item_type'                      => isset($params['item_type']) ? $params['item_type'] : 'quantity',
			'date'                           => $date,
			'expiry_date'                    => $expiry_date,
			'expiry_date_detail'             => isset($params['expiry_date_detail']) ? toDate($params['expiry_date_detail']) : null,
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

        if (! $expiry_date || $expiry_date === 'no_expiry_date') {
            $formatted['expiry_date_detail'] = null;
        } elseif ($expiry_date === 'expiry_on_date') {
            $formatted['expiry_date_detail'] = $expiry_date_detail;
        } else {
            $formatted['expiry_date_detail'] = date('Y-m-d', strtotime($date . ' +'.$expiry_date.' day'));
        }

        if (! $uuid) {
            $formatted['user_id'] = \Auth::user()->id;
            $formatted['uuid'] = Str::uuid();
            $formatted['upload_token'] = isset($params['upload_token']) ? $params['upload_token'] : null;
        }

        return $formatted;
    }

    /**
     * Validate quotation fields
     *
     * @param array $params
     * @param integer $id (optional)
     * @return void
     */
    private function validateFields($params = array(), $id = null)
    {
        $this->validateQuotationNumber($params, $id);

        $this->validateDates($params);

        $this->validateItems($params);

        $this->validateSubTotal($params);
    }

    /**
     * Validate quotation number
     *
     * @param array $params
     * @return void
     */
    private function validateQuotationNumber($params, $id = null)
    {
        $quotation_number = isset($params['quotation_number']) ? $params['quotation_number'] : null;

        $quotation = ($id) ? $this->findOrFail($id) : null;

        $query = $this->quotation->whereNotNull('id');

        if ($quotation) {
            if ($quotation->number == $quotation_number) {
                return;
            }

            $query->where('id','!=',$id);
        }

        if ($query->where(function($q) use($quotation_number) {
                $q->where('number', '>', $quotation_number)->orWhere('number','=',$quotation_number);
            })->count()) {
            throw ValidationException::withMessages(['quotation_number' => trans('quotation.invalid_quotation_number')]);
        }
    }

    /**
     * Validate quotation date
     *
     * @param array $params
     * @return void
     */
    private function validateDates($params)
    {
        $date = isset($params['date']) ? $params['date'] : null;
        $expiry_date = isset($params['expiry_date']) ? $params['expiry_date'] : null;
        $expiry_date_detail = isset($params['expiry_date_detail']) ? $params['expiry_date_detail'] : null;

        if ($expiry_date === 'expiry_on_date' && (! $expiry_date_detail || $date > $expiry_date_detail)) {
            throw ValidationException::withMessages(['expiry_date_detail' => trans('quotation.valid_expiry_date_detail_required')]);
        }
    }

    /**
     * Validate quotation items
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
            throw ValidationException::withMessages(['message' => trans('quotation.no_item_found')]);
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
                $errors['item_name_'.$key] = [trans('quotation.item_error')];
            }

            if (($item_type === 'quantity' || $item_type === 'hour') && (! is_numeric($quantity) || $quantity < 0)) {
                $errors['item_quantity_'.$key] = [trans('quotation.quantity_error')];
            }

            if (! is_numeric($unit_price) || $unit_price < 0) {
                $errors['item_unit_price_'.$key] = [trans('quotation.unit_price_error')];
            }

            if ($line_item_discount && (! is_numeric($discount) || $discount < 0)) {
                $errors['item_discount_'.$key] = [trans('quotation.discount_error')];
            }

            if ($line_item_tax && (! is_numeric($tax) || $tax < 0)) {
                $errors['item_tax_'.$key] = [trans('quotation.tax_error')];
            }
        }

        if (count($errors)) {
        	throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Validate quotation sub total
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
            $errors['subtotal_discount'] = [trans('quotation.discount_error')];
        }

        if ($subtotal_tax && (!is_numeric($subtotal_tax) || $subtotal_tax < 0)) {
            $errors['subtotal_tax'] = [trans('quotation.tax_error')];
        }

        if ($subtotal_shipping_and_handling && (!is_numeric($subtotal_shipping_and_handling) || $subtotal_shipping_and_handling < 0)) {
            $errors['subtotal_shipping_and_handling'] = [trans('quotation.shipping_and_handling_error')];
        }

        if (!is_numeric($total) || $total < 0) {
            $errors['message'] = [trans('quotation.total_error')];
        }

        if (count($errors)) {
        	throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Delete quotation.
     *
     * @param integer $id
     * @return bool|null
     */
    public function delete(Quotation $quotation)
    {
        return $quotation->delete();
    }

    /**
     * Delete multiple quotations.
     *
     * @param array $ids
     * @return bool|null
     */
    public function deleteMultiple($ids)
    {
        return $this->quotation->whereIn('id', $ids)->delete();
    }

    /**
     * Store quotation discussion.
     *
     * @param Quotation $quotation
     * @param array $params
     * @return void
     */
    public function storeDiscussion(Quotation $quotation, $params = array())
    {
        $comment = isset($params['comment']) ? $params['comment'] : null;
        $comment_id = isset($params['comment_id']) ? $params['comment_id'] : null;

        return $this->quotation_discussion->forceCreate([
        	'comment' => $comment,
        	'reply_id' => $comment_id,
        	'quotation_id' => $quotation->id,
        	'user_id' => \Auth::user()->id
        ]);
    }

    /**
     * Get quotation discussion.
     *
     * @param Quotation $quotation
     * @return QuotationDiscussion
     */
    public function getDiscussion(Quotation $quotation)
    {
    	return $this->quotation_discussion->with('user','user.profile','reply','reply.user','reply.user.profile')->filterByQuotationId($quotation->id)->whereNull('reply_id')->orderBy('created_at','desc')->get();
    }

    /**
     * Get quotation discussion.
     *
     * @param Quotation $quotation
     * @return QuotationDiscussion
     */
    public function deleteDiscussion(Quotation $quotation, $comment_id)
    {
        $comment = $this->quotation_discussion->filterById($comment_id)->filterByUserId(\Auth::user()->id)->first();

        if( !$comment)
            throw ValidationException::withMessages(['message' => trans('quotation.could_not_find_discussion')]);

        $comment->delete();
    }

    /**
     * Get graph data by status
     *
     * @return array
     */
    public function graphByStatus()
    {
        $status = array();
        foreach($this->quotation->select('status','is_draft','is_cancelled','expiry_date_detail')->get() as $quotation){
            if($quotation->is_draft)
                array_push($status,'draft');
            elseif($quotation->is_cancelled)
                array_push($status,'cancelled');
            elseif($quotation->status == 'sent' || $quotation->status == null)
                array_push($status,'sent');
            elseif($quotation->status == 'accepted')
                array_push($status,'accepted');
            elseif($quotation->status == 'rejected')
                array_push($status,'rejected');
            elseif($quotation->status == 'invoiced')
                array_push($status,'invoiced');
            elseif(!in_array($quotation->status,['accepted','rejected','invoiced']) && $quotation->expiry_date_detail < date('Y-m-d'))
                array_push($status,'expired');
        }

        $quotation_status = array();
        foreach(array_count_values($status) as $key => $value)
            $quotation_status[] = array('name' => trans('quotation.status_'.$key),'total' => $value,'color' => $this->getColorByStatus($key));

        return $quotation_status;
    }
}
