<?php

namespace App\Http\Controllers;

use App\Quotation;
use Illuminate\Http\Request;
use App\Repositories\ItemRepository;
use App\Repositories\UserRepository;
use App\Repositories\UploadRepository;
use App\Http\Requests\QuotationRequest;
use App\Repositories\AccountRepository;
use App\Repositories\CurrencyRepository;
use App\Repositories\EmailLogRepository;
use App\Repositories\QuotationRepository;
use App\Repositories\ActivityLogRepository;
use App\Http\Requests\QuotationEmailRequest;
use App\Repositories\ConfigurationRepository;
use App\Http\Requests\QuotationDiscussionRequest;

class QuotationController extends Controller
{
    protected $request;
    protected $repo;
    protected $activity;
    protected $user;
    protected $currency;
    protected $item;
    protected $email_log;
    protected $account;
    protected $upload;
    protected $config;
    protected $module = 'quotation';

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(
        Request $request,
        QuotationRepository $repo,
        ActivityLogRepository $activity,
        UserRepository $user,
        CurrencyRepository $currency,
        ItemRepository $item,
        EmailLogRepository $email_log,
        AccountRepository $account,
        UploadRepository $upload,
        ConfigurationRepository $config
    ) {
        $this->request   = $request;
        $this->repo      = $repo;
        $this->activity  = $activity;
        $this->user      = $user;
        $this->currency  = $currency;
        $this->item      = $item;
        $this->email_log = $email_log;
        $this->account   = $account;
        $this->upload    = $upload;
        $this->config    = $config;
    }

    /**
     * Used to fetch Pre-Requisites for Quotation
     * @get ("/api/quotation/pre-requisite")
     * @return Response
     */
    public function preRequisite()
    {
        $this->authorize('preRequisite', Quotation::class);

        $customers            = $this->user->selectAllCustomerByNameId();
        $currencies           = $this->currency->selectAllForCurrencyInput();
        $default_currency     = $this->currency->default();
        $items                = generateSelectOption($this->item->listAll());
        $item_details         = $this->item->getAll();
        $item_types           = generateTranslatedSelectOption(getVar('list')['item_type']);
        $default_item_type    = ['name' => trans('list.quantity'), 'id' => 'quantity'];
        $expiry_dates         = $this->repo->listExpiryDate();
        $new_quotation_number = $this->repo->getNewQuotationNumber();

        return $this->success(compact('customers', 'currencies', 'default_currency', 'items', 'item_details', 'item_types', 'default_item_type', 'expiry_dates', 'new_quotation_number'));
    }

    /**
     * Used to get all Quotations
     * @get ("/api/quotation")
     * @return Response
     */
    public function index()
    {
        $this->authorize('list', Quotation::class);

        return $this->ok($this->repo->paginate($this->request->all()));
    }

    /**
     * Used to store Quotation
     * @post ("/api/quotation")
     * @param ({
     *      @Parameter("uuid", type="string", required="optional", description="Required when update")
     * })
     * @return Response
     */
    public function store(QuotationRequest $request, $uuid = null)
    {
        $this->authorize($uuid ? 'update' : 'create', Quotation::class);

        $quotation = $this->repo->store($this->request->all(), $uuid ? : null);

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $quotation->id,
            'sub_module' => $quotation->quotation_number,
            'activity'   => 'added'
        ]);

        return $this->success(['message' => trans('quotation.saved'), 'quotation' => $quotation]);
    }

    /**
     * Used to get Quotation detail
     * @get ("/api/quotation/{id}")
     * @param ({
     *      @Parameter("id", type="integer", required="true", description="Id of Quotation"),
     * })
     * @return Response
     */
    public function show($uuid)
    {
        $this->authorize('view', Quotation::class);

        $quotation = $this->repo->findByUuidOrFail($uuid);

        $this->repo->accessible($quotation);

        $attachments =  $this->upload->getAttachment($this->module, $quotation->id);
        $countries = getVar('country');

        return $this->success(compact('quotation', 'attachments', 'countries'));
    }

    /**
     * Used to fetch Quotation detail
     * @get ("/api/quotation/{uuid}/detail")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Quotation"),
     * })
     * @return Response
     */
    public function fetchDetail($uuid)
    {
        $this->authorize('update', Quotation::class);

        $quotation = $this->repo->findByUuidOrFail($uuid);

        $this->repo->isCancelled($quotation);

        $this->repo->accessible($quotation);

        $rows = array();
        foreach ($quotation->quotationItem as $quotation_item) {
            $rows[] = array(
                'uuid'             => $quotation_item->uuid,
                'name'             => ($quotation_item->item_id) ? : '',
                'selected_item'    => ($quotation_item->item_id) ? ['name' => $quotation_item->Item->detail,'id' => $quotation_item->item_id] : [],
                'custom_name'      => ($quotation_item->item_id) ? '' : $quotation_item->name,
                'show_custom_item' => ($quotation_item->item_id) ? false : true,
                'quantity'         => formatNumber($quotation_item->quantity, config('config.quotation_line_item_quantity_decimal_place')),
                'unit_price'       => formatNumber($quotation_item->unit_price, $quotation->Currency->decimal_place),
                'discount'         => formatNumber($quotation_item->discount, config('config.quotation_line_item_discount_decimal_place')),
                'tax'              => formatNumber($quotation_item->tax, config('config.quotation_line_item_tax_decimal_place')),
                'amount'           => formatNumber($quotation_item->amount, $quotation->Currency->decimal_place),
                'description'      => $quotation_item->description
            );
        }

        $selected_currency  = $this->currency->selectAllForCurrencyInput()->find($quotation->currency_id);
        $selected_customer  = $quotation->Customer ? ['id' => $quotation->customer_id, 'name' => $quotation->Customer->name] : [];
        $selected_item_type = ['id' => $quotation->item_type, 'name' => trans('list.'.$quotation->item_type)];

        if ($quotation->expiry_date === -1) {
            $selected_expiry_date_name = trans('quotation.no_expiry_date');
        } elseif ($quotation->expiry_date === 0) {
            $selected_expiry_date_name = trans('quotation.expiry_on_quotation_date');
        } elseif ($quotation->expiry_date === 'expiry_on_date') {
            $selected_expiry_date_name = trans('quotation.expiry_on_date');
        } else {
            $selected_expiry_date_name = trans('quotation.expiry_in_days', ['day' => $quotation->expiry_date]);
        }

        $selected_expiry_date = ['id' => $quotation->expiry_date, 'name' => $selected_expiry_date_name];

        return $this->success(compact('quotation', 'rows', 'selected_currency', 'selected_customer', 'selected_item_type', 'selected_expiry_date'));
    }

    /**
     * Used to send Email
     * @post ("/api/quotation/{uuid}/email")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Quotation"),
     *      @Parameter("body", type="text", required="true", description="Body of Email"),
     *      @Parameter("subject", type="string", required="true", description="Subject of Email"),
     *      @Parameter("template_id", type="integer", required="true", description="Template Id of Email"),
     * })
     * @return Response
     */
    public function sendEmail(QuotationEmailRequest $request, $uuid)
    {
        $this->authorize('update', Quotation::class);

        $quotation = $this->repo->findByUuidOrFail($uuid);

        $this->repo->isCancelled($quotation);

        $this->repo->isDraft($quotation);

        $this->repo->accessible($quotation);

        $this->repo->sendMail($quotation, $this->request->all());

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $quotation->id,
            'sub_module' => 'mail',
            'activity'   => 'sent'
        ]);

        return $this->success(['message' => trans('template.mail_sent')]);
    }

    /**
     * Used to get Email log of Quotation
     * @get ("/api/quotation/{uuid}/email-log")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Quotation"),
     * })
     * @return Response
     */
    public function emailLog($uuid)
    {
        $this->authorize('update', Quotation::class);

        $quotation = $this->repo->findByUuidOrFail($uuid);

        $this->repo->isCancelled($quotation);

        $this->repo->isDraft($quotation);

        $this->repo->accessible($quotation);

        return $this->ok($this->email_log->paginate([
            'module'      => $this->module,
            'module_id'   => $quotation->id,
            'page_length' => request('page_length')
        ]));
    }

    /**
     * Used to get Email Detail of Quotation email log
     * @post ("/api/quotation/{uuid}/email-detail")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Quotation"),
     *      @Parameter("Id", type="string", required="true", description="Id of Email Log"),
     * })
     * @return Response
     */
    public function emailDetail($uuid)
    {
        $this->authorize('update', Quotation::class);

        $quotation = $this->repo->findByUuidOrFail($uuid);

        $this->repo->isCancelled($quotation);

        $this->repo->isDraft($quotation);

        $this->repo->accessible($quotation);

        return $this->ok($this->email_log->findByModuleAndModuleIdOrFail(request('id'), $this->module, $quotation->id));
    }

    /**
     * Used to send Quotation
     * @post ("/api/quotation/{uuid}/send")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Quotation"),
     * })
     * @return Response
     */
    public function send($uuid)
    {
        $this->authorize('update', Quotation::class);

        $quotation = $this->repo->findByUuidOrFail($uuid);

        $this->repo->isCancelled($quotation);

        $this->repo->accessible($quotation);

        $quotation = $this->repo->send($quotation);

        $this->activity->record([
            'module'        => $this->module,
            'module_id'     => $quotation->id,
            'sub_module'    => $quotation->quotation_number,
            'activity'      => 'sent'
        ]);

        return $this->success(['message' => trans('quotation.sent')]);
    }

    /**
     * Used to cancel Quotation
     * @post ("/api/quotation/{uuid}/cancel")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Quotation"),
     * })
     * @return Response
     */
    public function cancel($uuid)
    {
        $this->authorize('update', Quotation::class);

        $quotation = $this->repo->findByUuidOrFail($uuid);

        $this->repo->accessible($quotation);

        $this->repo->isCancelled($quotation);

        $this->repo->isDraft($quotation);

        $this->repo->isInvoiced($quotation);

        $quotation = $this->repo->cancel($quotation);

        $this->activity->record([
            'module'        => $this->module,
            'module_id'     => $quotation->id,
            'sub_module'    => $quotation->quotation_number,
            'activity'      => 'cancelled'
        ]);

        return $this->success(['message' => trans('quotation.cancelled')]);
    }

    /**
     * Used to undo cancel Quotation
     * @post ("/api/quotation/{uuid}/undo-cancel")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Quotation"),
     * })
     * @return Response
     */
    public function undoCancel($uuid)
    {
        $this->authorize('update', Quotation::class);

        $quotation = $this->repo->findByUuidOrFail($uuid);

        $this->repo->isDraft($quotation);

        $this->repo->accessible($quotation);

        $quotation = $this->repo->undoCancel($quotation);

        $this->activity->record([
            'module'        => $this->module,
            'module_id'     => $quotation->id,
            'sub_module'    => $quotation->quotation_number,
            'activity'      => 'undo_cancelled'
        ]);

        return $this->success(['message' => trans('quotation.saved')]);
    }

    /**
     * Used to print Quotation
     * @get ("/quotation/{uuid}/print")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Quotation"),
     * })
     * @return Response
     */
    public function print($uuid)
    {
        $quotation = $this->repo->findByUuidOrFail($uuid);

        $this->repo->accessible($quotation);

        $action = 'print';
        $quotation_color = $this->repo->getColor($quotation);
        $quotation_status = $this->repo->getPrintStatus($quotation);

        $this->activity->record([
            'module'        => $this->module,
            'module_id'     => $quotation->id,
            'sub_module'    => $quotation->quotation_number,
            'activity'      => 'printed'
        ]);
        
        return view('quotation.print', compact('quotation', 'action', 'quotation_color','quotation_status'));
    }

    /**
     * Used to generate PDF Quotation
     * @get ("/quotation/{uuid}/print")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Quotation"),
     * })
     * @return Response
     */
    public function pdf($uuid)
    {
        $quotation = $this->repo->findByUuidOrFail($uuid);

        $this->repo->accessible($quotation);

        $action = 'pdf';
        $quotation_color = $this->repo->getColor($quotation);
        $quotation_status = $this->repo->getPrintStatus($quotation);

        $this->activity->record([
            'module'        => $this->module,
            'module_id'     => $quotation->id,
            'sub_module'    => $quotation->quotation_number,
            'activity'      => 'saved_as_pdf'
        ]);

        $pdf = \PDF::loadView('quotation.print', compact('quotation', 'quotation_color', 'action','quotation_status'));
        return $pdf->download($uuid.'.pdf');
    }

    /**
     * Used to mark Quotation as sent
     * @post ("/api/quotation/{uuid}/mark-as-sent")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Quotation"),
     * })
     * @return Response
     */
    public function markAsSent($uuid)
    {
        $this->authorize('update', Quotation::class);

        $quotation = $this->repo->findByUuidOrFail($uuid);

        $this->repo->isCancelled($quotation);

        $this->repo->accessible($quotation);

        $quotation = $this->repo->markAsSent($quotation);

        $this->activity->record([
            'module'        => $this->module,
            'module_id'     => $quotation->id,
            'sub_module'    => $quotation->quotation_number,
            'activity'      => 'marked_as_sent'
        ]);

        return $this->success(['message' => trans('quotation.saved')]);
    }

    /**
     * Used to copy Quotation
     * @post ("/api/quotation/{uuid}/copy")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Quotation"),
     * })
     * @return Response
     */
    public function copy($uuid)
    {
        $this->authorize('update', Quotation::class);

        $quotation = $this->repo->findByUuidOrFail($uuid);

        $this->repo->accessible($quotation);

        $new_quotation = $this->repo->copy($quotation);

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $new_quotation->id,
            'sub_module' => $quotation->quotation_number,
            'activity'   => 'copied'
        ]);

        return $this->success(['message' => trans('quotation.copied'),'uuid' => $new_quotation->uuid]);
    }

    /**
     * Used to delete Quotation
     * @delete ("/api/quotation/{uuid}")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Quotation"),
     * })
     * @return Response
     */
    public function destroy($uuid)
    {
        $this->authorize('update', Quotation::class);

        $quotation = $this->repo->findByUuidOrFail($uuid);

        $this->repo->isInvoiced($quotation);

        $this->repo->accessible($quotation);

        $this->upload->delete($this->module, $quotation->id);

        $this->activity->record([
            'module'        => $this->module,
            'module_id'     => $quotation->id,
            'sub_module'    => $quotation->quotation_number,
            'activity'      => 'deleted'
        ]);

        $this->repo->delete($quotation);

        return $this->success(['message' => trans('quotation.deleted')]);
    }

    /**
     * Used to download Quotation attachment
     * @delete ("/quotation/{quotation_uuid}/attachment/{attachment_uuid}/download")
     * @param ({
     *      @Parameter("quotation_uuid", type="string", required="true", description="Unique Id of Quotation"),
     *      @Parameter("attachment_uuid", type="string", required="true", description="Unique Id of Attachment"),
     * })
     * @return Response
     */
    public function download($quotation_uuid, $attachment_uuid)
    {
        $quotation = $this->repo->findByUuidOrFail($quotation_uuid);

        $this->repo->accessible($quotation);

        $attachment =  $this->upload->getAttachment($this->module, $quotation->id, $attachment_uuid);

        try {
            \Storage::exists($attachment->filename);
        } catch (\Exception $e) {
        }

        $this->activity->record([
            'module'        => 'attachment',
            'module_id'     => $attachment->id,
            'sub_module'    => $this->module,
            'sub_module_id' => $quotation->id,
            'activity'      => 'downloaded'
        ]);

        $download_path = storage_path('app/'.$attachment->filename);
        return response()->download($download_path, $attachment->user_filename);
    }

    /**
     * Used to perform customer action in Quotation
     * @post ("/api/quotation/{uuid}/action")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Quotation"),
     *      @Parameter("action", type="string", required="true", description="Action to be peformed, can be accept or reject"),
     * })
     * @return Response
     */

    public function customerAction($uuid)
    {
        $quotation = $this->repo->findByUuidOrFail($uuid);

        $this->repo->accessible($quotation);

        $this->repo->isCancelled($quotation);

        $this->repo->isDraft($quotation);

        $quotation = $this->repo->customerAction($quotation, $this->request->all());

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $quotation->id,
            'sub_module' => $quotation->quotation_number,
            'activity'   => $quotation->status
        ]);

        return $this->success(['message' => trans('quotation.updated'),'status' => $quotation->status]);
    }

    /**
     * Used to convert Quotation into Invoice
     * @post ("/api/quotation/{uuid}/convert-to-invoice")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Quotation"),
     * })
     * @return Response
     */

    public function convertToInvoice($uuid)
    {
        $this->authorize('update', Quotation::class);

        $quotation = $this->repo->findByUuidOrFail($uuid);

        $this->repo->accessible($quotation);

        $this->repo->isCancelled($quotation);

        $this->repo->isDraft($quotation);

        $this->repo->isInvoiced($quotation);

        $invoice = $this->repo->convertToInvoice($quotation);

        $this->activity->record([
            'module'     => $this->module,
            'module_id'  => $quotation->id,
            'sub_module' => $quotation->quotation_number,
            'activity'   => 'converted_to_invoice'
        ]);

        return $this->success(['message' => trans('quotation.converted'),'invoice_uuid' => $invoice->uuid]);
    }

    /**
     * Used to post discussion in Quotation
     * @post ("/api/quotation/{uuid}/discussion")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Quotation"),
     *      @Parameter("comment", type="string", required="true", description="Comment from User"),
     * })
     * @return Response
     */

    public function discussion(QuotationDiscussionRequest $request, $uuid)
    {
        $quotation = $this->repo->findByUuidOrFail($uuid);

        $this->repo->accessible($quotation);

        $quotation_discussion = $this->repo->storeDiscussion($quotation, $this->request->all());

        $this->activity->record([
            'module'        => 'quotation_discussion',
            'module_id'     => $quotation_discussion->id,
            'sub_module'    => $this->module,
            'sub_module_id' => $quotation->id,
            'activity'      => 'commented'
        ]);

        return $this->success(['message' => trans('quotation.comment_posted')]);
    }

    /**
     * Used to get discussions of Quotation
     * @get ("/api/quotation/{uuid}/discussion")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Quotation"),
     * })
     * @return Response
     */

    public function getDiscussion($uuid)
    {
        $quotation = $this->repo->findByUuidOrFail($uuid);

        $this->repo->accessible($quotation);

        return $this->repo->getDiscussion($quotation);
    }

    /**
     * Used to delete Quotation Discussion
     * @delete ("/api/quotation/{uuid}/discussion/{id}")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="Unique Id of Quotation"),
     *      @Parameter("id", type="integer", required="true", description="Id of Quotation Discussion"),
     * })
     * @return Response
     */

    public function destroyDiscussion($uuid, $id)
    {
        $quotation = $this->repo->findByUuidOrFail($uuid);

        $this->repo->accessible($quotation);

        $this->repo->deleteDiscussion($quotation, $id);

        $this->activity->record([
            'module'        => 'quotation_discussion',
            'module_id'     => $id,
            'sub_module'    => $this->module,
            'sub_module_id' => $quotation->id,
            'activity'      => 'deleted'
        ]);

        return $this->success(['message' => trans('quotation.quotation_discussion_deleted')]);
    }
}
