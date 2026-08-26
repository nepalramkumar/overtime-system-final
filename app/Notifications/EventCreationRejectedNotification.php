<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EventCreationRejectedNotification extends Notification
{
    use Queueable;

    protected $event;
    protected $reason;

    public function __construct(Event $event, string $reason)
    {
        $this->event = $event;
        $this->reason = $reason;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Event Reject भयो: ' . $this->event->event_name)
            ->greeting('नमस्ते ' . ($notifiable->name ?? '') . ',')
            ->line('तपाईंले दर्ता गर्नुभएको "' . $this->event->event_name . '" अस्वीकृत भयो ।')
            ->line('कारण: ' . $this->reason)
            ->line('कृपया आवश्यक सच्याएर फेरि स्वीकृतीको लागि पठाउनुहोस्।')
            ->action('कार्यक्रम हेर्नुहोस्', route('events.list'));
    }

    public function toArray($notifiable): array
    {
        return [
            'title'   => 'Event Reject भयो',
            'message' => '"' . $this->event->event_name . '" अस्वीकृत भयो । कारण: ' . $this->reason,
            'url'     => route('events.list'),
            'event_id' => $this->event->id,
        ];
    }
}
