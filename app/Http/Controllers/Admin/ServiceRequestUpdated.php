<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;
use App\Models\ServiceRequest;

class ServiceRequestUpdated extends Notification
{
    use Queueable;

    public $serviceRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(ServiceRequest $serviceRequest)
    {
        $this->serviceRequest = $serviceRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        $title = 'Aggiornamento Pratica';
        $body = "La tua pratica '{$this->serviceRequest->service_name}' è stata aggiornata. Nuovo stato: {$this->serviceRequest->status}.";
        $url = route('home'); // L'URL che si aprirà al click sulla notifica

        return (new WebPushMessage)
            ->title($title)
            ->icon(asset('images/icons/icon-192x192.png')) // Icona della notifica
            ->body($body)
            ->data(['url' => $url]); // Dati extra, usati dal Service Worker
    }
}