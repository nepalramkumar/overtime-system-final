<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EventCreatedNotification extends Notification
{
    use Queueable;

    protected $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('नयाँ Event Approval बाँकी: ' . $this->event->event_name)
            ->greeting('नमस्ते ' . ($notifiable->name ?? '') . ',')
            ->line('"' . $this->event->event_name . '" कार्यक्रम दर्ता भई तपाईं समक्ष स्वीकृतीको लागि पेश भएको छ ।')
                        ->action('कार्यक्रम हेर्नुहोस्', route('events.list'));
    }

    public function toArray($notifiable): array
    {
        return [
            'title'   => 'नयाँ Event Approval बाँकी',
            'message' => '"' . $this->event->event_name . '" कार्यक्रम दर्ता भई तपाईं समक्ष स्वीकृतीको लागि पेश भएको छ ।',
            'url'     => route('events.list'),
            'event_id' => $this->event->id,
        ];
    }
}
