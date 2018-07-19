<?php

namespace App\Jobs;

use App\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use App\Repositories\QuotationRepository;
use App\Repositories\EmailLogRepository;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Repositories\EmailTemplateRepository;

class SendQuotationMail implements ShouldQueue
{
    protected $to;
    protected $config;
    protected $quotation;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($to, $config, Quotation $quotation)
    {
        $this->to = $to;
        $this->config = $config;
        $this->quotation = $quotation;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(EmailLogRepository $email, EmailTemplateRepository $emailTemplate, QuotationRepository $repo)
    {
        $quotation = $this->quotation;
        $slug = isset($this->config['slug']) ? $this->config['slug'] : null;

        $quotation_color     = $repo->getColor($quotation);
        $quotation_status    = $repo->getPrintStatus($quotation);
        $action              = 'pdf';
        $attachment_document = \PDF::loadView('quotation.print', compact('quotation', 'quotation_color', 'action','quotation_status'));

        if ($slug) {
            $template        = $emailTemplate->findBySlug($this->config['slug']);
            $mail_data       = $emailTemplate->getContent(['template' => $template, 'quotation' => $quotation]);
            $body            = $mail_data['body'];
            $mail['subject'] = $mail_data['subject'];
        } else {
            $body            = $this->config['body'];
            $mail['subject'] = $this->config['subject'];
        }

        $mail['email']               = $this->to;
        $mail['attachment_document'] = $attachment_document;
        $mail['attachment_name']     = 'Quotation_'.$quotation->quotation_number.'.pdf';

        \Mail::send('emails.email', compact('body'), function ($message) use ($mail) {
            $message->attachData($mail['attachment_document']->output(), $mail['attachment_name']);
            $message->to($mail['email'])->subject($mail['subject']);
        });

        $email->record([
            'to'        => $mail['email'],
            'subject'   => $mail['subject'],
            'body'      => $body,
            'module'    => 'quotation',
            'module_id' => $this->quotation->id
        ]);
    }
}
