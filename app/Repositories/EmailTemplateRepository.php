<?php
namespace App\Repositories;

use App\EmailTemplate;
use App\Repositories\InvoiceRepository;
use App\Repositories\QuotationRepository;
use App\Repositories\ConfigurationRepository;

class EmailTemplateRepository
{
    protected $email_template;
    protected $config;
    protected $invoice;
    protected $quotation;

    /**
     * Instantiate a new instance.
     *
     * @return void
     */
    public function __construct(
        EmailTemplate $email_template,
        ConfigurationRepository $config,
        InvoiceRepository $invoice,
        QuotationRepository $quotation
    ) {
        $this->email_template = $email_template;
        $this->config        = $config;
        $this->invoice = $invoice;
        $this->quotation = $quotation;
    }

    /**
     * Find email template with given id or throw an error.
     *
     * @param integer $id
     * @return EmailTemplate
     */
    public function findOrFail($id)
    {
        $email_template = $this->email_template->find($id);

        if (! $email_template) {
            throw ValidationException::withMessages(['message' => trans('template.could_not_find')]);
        }

        return $email_template;
    }

    /**
     * Find by slug.
     *
     * @return EmailTemplate
     */
    public function findBySlug($slug)
    {
        return $this->email_template->filterBySlug($slug)->first();
    }

    /**
     * List email template by category.
     *
     * @param string $category
     * @return array
     */
    public function listByCategory($category = null)
    {
        return $this->email_template->whereCategory($category ? : 'user')->get(['name','id']);
    }

    /**
     * Paginate all email templates using given params.
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($params)
    {
        $sort_by     = isset($params['sort_by']) ? $params['sort_by'] : 'created_at';
        $order      = isset($params['order']) ? $params['order'] : 'desc';
        $page_length = isset($params['page_length']) ? $params['page_length'] : config('config.page_length');

        return $this->email_template->orderBy($sort_by, $order)->paginate($page_length);
    }

    /**
     * Create a new email template.
     *
     * @param array $params
     * @return EmailTemplate
     */
    public function create($params)
    {
        return $this->email_template->forceCreate($this->formatParams($params));
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
        if ($action === 'create') {
            $formatted = [
                'name'     => isset($params['name']) ? $params['name'] : null,
                'category' => isset($params['category']) ? $params['category'] : null,
                'slug'     => isset($params['category']) ? createSlug($params['category']) : null
            ];
        } elseif ($action === 'update') {
            $formatted = [
                'subject' => isset($params['subject']) ? $params['subject'] : null,
                'body'    => isset($params['body']) ? $params['body'] : null
            ];
        }

        return $formatted;
    }

    /**
     * Update given email template.
     *
     * @param EmailTemplate $email_template
     * @param array $params
     *
     * @return EmailTemplate
     */
    public function update(EmailTemplate $email_template, $params)
    {
        $email_template->forceFill($this->formatParams($params, 'update'))->save();

        return $email_template;
    }

    /**
     * Find email template with given id which is deletable or throw an error.
     *
     * @param integer $id
     * @return EmailTemplate
     */
    public function deletable($id)
    {
        return $this->email_template->whereId($id)->whereIsDefault(0)->firstOrFail();
    }

    /**
     * Delete email template.
     *
     * @param integer $id
     * @return bool|null
     */
    public function delete(EmailTemplate $email_template)
    {
        return $email_template->delete();
    }

    /**
     * Delete multiple email template.
     *
     * @param array $ids
     * @return bool|null
     */
    public function deleteMultiple($ids)
    {
        return $this->email_template->whereIn('id', $ids)->delete();
    }

    /**
     * Get content for Email.
     *
     * @param array $params
     * @return array
     */
    public function getContent($params = array())
    {
        $template = $params['template'];
        $user     = isset($params['user']) ? $params['user']  : null;
        $password = isset($params['password']) ? $params['password'] : '';

        $body    = $template->body;
        $subject = $template->subject;

        $company_logo    = getCompanyLogo();
        $company_address = getCompanyAddress();

        $body = str_replace('[COMPANY_LOGO]', $company_logo, $body);
        
        $body    = str_replace('[COMPANY_NAME]', config('config.company_name'), $body);
        $subject = str_replace('[COMPANY_NAME]', config('config.company_name'), $subject);
        
        $body    = str_replace('[COMPANY_EMAIL]', config('config.email'), $body);
        $subject = str_replace('[COMPANY_EMAIL]', config('config.email'), $subject);
        
        $body    = str_replace('[COMPANY_PHONE]', config('config.phone'), $body);
        $subject = str_replace('[COMPANY_PHONE]', config('config.phone'), $subject);
        
        $body    = str_replace('[COMPANY_WEBSITE]', config('config.website'), $body);
        $subject = str_replace('[COMPANY_WEBSITE]', config('config.website'), $subject);
        
        $body    = str_replace('[COMPANY_ADDRESS]', $company_address, $body);
        $subject = str_replace('[COMPANY_ADDRESS]', $company_address, $subject);
        
        $body    = str_replace('[CURRENT_DATE]', showDate(date('Y-m-d')), $body);
        $subject = str_replace('[CURRENT_DATE]', showDate(date('Y-m-d')), $subject);
        
        $body    = str_replace('[CURRENT_DATE_TIME]', showDateTime(date('Y-m-d H:i:s')), $body);
        $subject = str_replace('[CURRENT_DATE_TIME]', showDateTime(date('Y-m-d H:i:s')), $subject);

        if (($template->category === 'customer' || $template->category === 'staff') && $user) {

            $body    = str_replace('[NAME]', ($user->name) ? : '-', $body);
            $subject = str_replace('[NAME]', ($user->name) ? : '-', $subject);

            $body    = str_replace('[PASSWORD]', $password, $body);
            $subject = str_replace('[PASSWORD]', $password, $subject);

            $body    = str_replace('[EMAIL]', $user->email, $body);
            $subject = str_replace('[EMAIL]', $user->email, $subject);

            $body    = str_replace('[DATE_OF_BIRTH]', ($user->Profile->date_of_birth) ? showDate($user->Profile->date_of_birth) : '-', $body);
            $subject = str_replace('[DATE_OF_BIRTH]', ($user->Profile->date_of_birth) ? showDate($user->Profile->date_of_birth) : '-', $subject);

            if ($template->category === 'staff') {
                $body = str_replace('[DEPARTMENT]', ($user->Profile->designation_id) ? $user->Profile->Designation->Department->name : '-', $body);
                $subject = str_replace('[DEPARTMENT]', ($user->Profile->designation_id) ? $user->Profile->Designation->Department->name : '-', $subject);

                $body = str_replace('[DESIGNATION]', ($user->Profile->designation_id) ? $user->Profile->Designation->name : '-', $body);
                $subject = str_replace('[DESIGNATION]', ($user->Profile->designation_id) ? $user->Profile->Designation->name : '-', $subject);
            }
                
            $body    = str_replace('[DATE_OF_ANNIVERSARY]', ($user->Profile->date_of_anniversary) ? showDate($user->Profile->date_of_anniversary) : '-', $body);
            $subject = str_replace('[DATE_OF_ANNIVERSARY]', ($user->Profile->date_of_anniversary) ? showDate($user->Profile->date_of_anniversary) : '-', $subject);

        } elseif ($template->category === 'invoice' || $template->category === 'invoice-payment' || $template->category === 'quotation') {
            if ($template->category === 'invoice') {

                $invoice = $params['invoice'];

            } elseif ($template->category === 'quotation') {

                $quotation        = $params['quotation'];
                $customer_name    = $quotation->Customer->name;
                $customer_email   = $quotation->Customer->email;
                $customer_phone   = $quotation->Customer->Profile->phone;
                $reference_number = $quotation->reference_number;

            } elseif ($template->category === 'invoice-payment') {

                $transaction = $params['transaction'];
                $invoice     = $transaction->Invoice;

            }

            if ($template->category === 'invoice' || $template->category === 'invoice-payment') {

                $customer_name    = $invoice->Customer->name;
                $customer_email   = $invoice->Customer->email;
                $customer_phone   = $invoice->Customer->Profile->phone;
                $reference_number = $invoice->reference_number;

            }


            $body    = str_replace('[CUSTOMER_NAME]', $customer_name, $body);
            $subject = str_replace('[CUSTOMER_NAME]', $customer_name, $subject);
            
            $body    = str_replace('[CUSTOMER_EMAIL]', $customer_email, $body);
            $subject = str_replace('[CUSTOMER_EMAIL]', $customer_email, $subject);
            
            $body    = str_replace('[CUSTOMER_PHONE]', $customer_phone, $body);
            $subject = str_replace('[CUSTOMER_PHONE]', $customer_phone, $subject);
            
            $body    = str_replace('[REFERENCE_NUMBER]', $reference_number, $body);
            $subject = str_replace('[REFERENCE_NUMBER]', $reference_number, $subject);

            if ($template->category === 'invoice' || $template->category === 'invoice-payment') {

                $body         = str_replace('[INVOICE_NUMBER]', $invoice->invoice_number, $body);
                $subject      = str_replace('[INVOICE_NUMBER]', $invoice->invoice_number, $subject);
                
                $body         = str_replace('[INVOICE_DATE]', ($invoice->date) ? showDate($invoice->date) : '', $body);
                $subject      = str_replace('[INVOICE_DATE]', ($invoice->date) ? showDate($invoice->date) : '', $subject);
                
                $body         = str_replace('[INVOICE_DUE_DATE]', showDate($invoice->due_date_detail), $body);
                $subject      = str_replace('[INVOICE_DUE_DATE]', showDate($invoice->due_date_detail), $subject);
                
                $body         = str_replace('[INVOICE_TOTAL]', currency($invoice->total,$invoice->Currency, 1), $body);
                $subject      = str_replace('[INVOICE_TOTAL]', currency($invoice->total,$invoice->Currency, 1), $subject);
                
                $invoice_link = '<a href="'.url('/invoice/'.$invoice->uuid).'">here</a>';
                
                $body         = str_replace('[INVOICE_LINK]', $invoice_link, $body);
                $subject      = str_replace('[INVOICE_LINK]', $invoice_link, $subject);

            } elseif ($template->category === 'quotation') {

                $body           = str_replace('[QUOTATION_NUMBER]', $quotation->quotation_number, $body);
                $subject        = str_replace('[QUOTATION_NUMBER]', $quotation->quotation_number, $subject);
                
                $body           = str_replace('[QUOTATION_DATE]', ($quotation->date) ? showDate($quotation->date) : '', $body);
                $subject        = str_replace('[QUOTATION_DATE]', ($quotation->date) ? showDate($quotation->date) : '', $subject);
                
                $body           = str_replace('[QUOTATION_EXPIRY_DATE]', showDate($quotation->expiry_date), $body);
                $subject        = str_replace('[QUOTATION_EXPIRY_DATE]', showDate($quotation->expiry_date), $subject);
                
                $body           = str_replace('[QUOTATION_TOTAL]', currency($quotation->total,$quotation->Currency, 1), $body);
                $subject        = str_replace('[QUOTATION_TOTAL]', currency($quotation->total,$quotation->Currency, 1), $subject);
                
                $quotation_link = '<a href="'.url('/quotation/'.$quotation->uuid).'">here</a>';
                
                $body           = str_replace('[QUOTATION_LINK]', $quotation_link, $body);
                $subject        = str_replace('[QUOTATION_LINK]', $quotation_link, $subject);

            } 

            if ($template->category === 'invoice-payment') {

                $body    = str_replace('[PAYMENT_AMOUNT]', currency($transaction->amount,$invoice->Currency, 1), $body);
                $subject = str_replace('[PAYMENT_AMOUNT]', currency($transaction->amount,$invoice->Currency, 1), $subject);
                
                $body    = str_replace('[PAYMENT_SOURCE]', strtoupper($transaction->source), $body);
                $subject = str_replace('[PAYMENT_SOURCE]', strtoupper($transaction->source), $subject);
                
                $body    = str_replace('[TRANSACTION_TOKEN]', $transaction->token, $body);
                $subject = str_replace('[TRANSACTION_TOKEN]', $transaction->token, $subject);
                
                $body    = str_replace('[TRANSACTION_DATE]', showDateTime($transaction->created_at), $body);
                $subject = str_replace('[TRANSACTION_DATE]', showDateTime($transaction->created_at), $subject);
                
                $body    = str_replace('[TRANSACTION_FAILURE_REASON]', toWord($transaction->gateway_response), $body);
                $subject = str_replace('[TRANSACTION_FAILURE_REASON]', toWord($transaction->gateway_response), $subject);
                
                $body    = str_replace('[PAYMENT_DATE]', showDate($transaction->date), $body);
                $subject = str_replace('[PAYMENT_DATE]', showDate($transaction->date), $subject);
                
                $body    = str_replace('[PAYMENT_METHOD]', ($transaction->payment_method_id) ? $transaction->PaymentMethod->name : '', $body);
                $subject = str_replace('[PAYMENT_METHOD]', ($transaction->payment_method_id) ? $transaction->PaymentMethod->name : '', $subject);

            }
        }

        $mail_data['body']    = $body;
        $mail_data['subject'] = $subject;

        return $mail_data;
    }
}
