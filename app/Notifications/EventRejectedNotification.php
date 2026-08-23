<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EventRejectedNotification extends Notification
{
    use Queueable;

    protected $event;
    protected $reason;
    protected $stage;

    public function __construct(Event $event, string $reason, string $stage)
    {
        $this->event = $event;
        $this->reason = $reason;
        $this->stage = $stage; // 'सिफारिस' वा 'स्वीकृति'
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('OT Reject भयो: ' . $this->event->event_name)
            ->greeting('नमस्ते ' . ($notifiable->name ?? '') . ',')
            ->line('"' . $this->event->event_name . '" कार्यक्रम ' . $this->stage . ' चरणमा Reject भएको छ।')
            ->line('कारण: ' . $this->reason)
            ->action('कार्यक्रम सच्याउनुहोस्', route('events.list'))
            ->line('कृपया आवश्यक सच्याएर फेरि Submit गर्नुहोस्।');
    }

    public function toArray($notifiable): array
    {
        return [
            'title'   => 'OT Reject भयो',
            'message' => '"' . $this->event->event_name . '" कार्यक्रम ' . $this->stage . ' चरणमा Reject भयो। कारण: ' . $this->reason,
            'url'     => route('events.list'),
            'event_id' => $this->event->id,
        ];
    }
}
