<?php

namespace App\Jobs;

use App\Invoice;
use App\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use App\Repositories\InvoiceRepository;
use App\Repositories\EmailLogRepository;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Repositories\EmailTemplateRepository;

class SendInvoiceMail implements ShouldQueue
{
    protected $to;
    protected $config;
    protected $invoice;
    protected $transaction;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($to, $config, Invoice $invoice, Transaction $transaction = null)
    {
        $this->to = $to;
        $this->config = $config;
        $this->invoice = $invoice;
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(EmailLogRepository $email, EmailTemplateRepository $emailTemplate, InvoiceRepository $repo)
    {
        $invoice = $this->invoice;
        $slug = isset($this->config['slug']) ? $this->config['slug'] : null;
        $transaction = $this->transaction;

        $invoice_color       = $repo->getColor($invoice);
        $invoice_status      = $repo->getPrintStatus($invoice);
        $action              = 'pdf';
        $attachment_document = \PDF::loadView('invoice.print', compact('invoice', 'invoice_color', 'action','invoice_status'));

        if ($slug) {
            $template        = $emailTemplate->findBySlug($this->config['slug']);
            $mail_data       = $emailTemplate->getContent(['template' => $template, 'invoice' => $invoice, 'transaction' => $transaction]);
            $body            = $mail_data['body'];
            $mail['subject'] = $mail_data['subject'];
        } else {
            $body            = $this->config['body'];
            $mail['subject'] = $this->config['subject'];
        }

        $mail['email']               = $this->to;
        $mail['attachment_document'] = $attachment_document;
        $mail['attachment_name']     = 'Invoice_'.$invoice->invoice_number.'.pdf';

        \Mail::send('emails.email', compact('body'), function ($message) use ($mail) {
            $message->attachData($mail['attachment_document']->output(), $mail['attachment_name']);
            $message->to($mail['email'])->subject($mail['subject']);
        });

        $email->record([
            'to'        => $mail['email'],
            'subject'   => $mail['subject'],
            'body'      => $body,
            'module'    => 'invoice',
            'module_id' => $this->invoice->id
        ]);
    }
}
