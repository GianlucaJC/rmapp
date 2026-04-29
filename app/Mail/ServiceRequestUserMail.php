<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class ServiceRequestUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $serviceTitle;
    public $serviceDescription;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $serviceTitle, string $serviceDescription)
    {
        $this->user = $user;
        $this->serviceTitle = $serviceTitle;
        $this->serviceDescription = $serviceDescription;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Conferma richiesta servizio LazioAPP: ' . $this->serviceTitle,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(view: 'emails.service_request_user');
    }
}