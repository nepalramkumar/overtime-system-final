<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EventApprovedForEntryNotification extends Notification
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
            ->subject('Event Approve भयो: ' . $this->event->event_name)
            ->greeting('नमस्ते ' . ($notifiable->name ?? '') . ',')
            ->line('तपाईंले दर्ता गर्नुभएको "' . $this->event->event_name . '" स्वीकृत भयो ।')
            ->line('अब यो कार्यक्रममा सबै Staff ले OT Entry गर्न मिल्नेछ।')
            ->action('कार्यक्रम हेर्नुहोस्', route('events.list'));
    }

    public function toArray($notifiable): array
    {
        return [
            'title'   => 'Event Approve भयो',
            'message' => '"' . $this->event->event_name . '" स्वीकृत भयो ।',
            'url'     => route('events.list'),
            'event_id' => $this->event->id,
        ];
    }
}
