<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class ServiceRequestAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $serviceTitle;
    public $serviceDescription;
    public $fictitiousUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $serviceTitle, string $serviceDescription, string $fictitiousUrl)
    {
        $this->user = $user;
        $this->serviceTitle = $serviceTitle;
        $this->serviceDescription = $serviceDescription;
        $this->fictitiousUrl = $fictitiousUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Richiesta nuovo servizio da LazioAPP',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(view: 'emails.service_request_admin');
    }
}