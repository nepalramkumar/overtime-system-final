<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EventSubmittedNotification extends Notification
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
            ->subject('OT सिफारिस बाँकी: ' . $this->event->event_name)
            ->greeting('नमस्ते ' . ($notifiable->name ?? '') . ',')
            ->line('"OT को सिफासिको लागि ' . $this->event->event_name . '" कार्यक्रम पेश भएको छ र सिफारिस गर्न तलको link click गर्नुहोस ।')
            ->action('कार्यक्रम हेर्नुहोस्', route('events.list'))
            ->line('वा Reject  गर्नको लागि पनि माथि कै link click गर्नुहोस।');
    }

    public function toArray($notifiable): array
    {
        return [
            'title'   => 'OT सिफारिस बाँकी',
            'message' => '"OT को सिफासिको लागि ' . $this->event->event_name . '" कार्यक्रम पेश भएको छ, सिफारिस गर्न यँहा click गर्नुहोस।',
            'url'     => route('events.list'),
            'event_id' => $this->event->id,
        ];
    }
}
