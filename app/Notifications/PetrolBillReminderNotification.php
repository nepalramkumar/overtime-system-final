<?php

namespace App\Notifications;

use App\Models\PetrolMonth;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Carbon\Carbon;

class PetrolBillReminderNotification extends Notification
{
    use Queueable;

    protected $month;
    protected $deadline;

    public function __construct(PetrolMonth $month, Carbon $deadline)
    {
        $this->month = $month;
        $this->deadline = $deadline;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Petrol Bill Entry बाँकी: ' . $this->month->month . ' ' . $this->month->year)
            ->greeting('नमस्ते ' . ($notifiable->name ?? '') . ',')
            ->line('तपाईंले ' . $this->month->month . ' ' . $this->month->year . ' महिनाको Petrol Bill अझै दर्ता गर्नुभएको छैन।')
            ->line('कृपया ' . adToBs($this->deadline->format('Y-m-d')) . ' (BS) / ' . $this->deadline->format('Y-m-d') . ' (AD) भित्र दर्ता गरिसक्नुहोस्, नत्र यो महिनाको Bill दर्ता गर्न पाइने छैन।')
            ->action('Petrol Bill दर्ता गर्नुहोस्', route('petrol.bills.create'));
    }

    public function toArray($notifiable): array
    {
        return [
            'title'   => 'Petrol Bill Entry बाँकी',
            'message' => $this->month->month . ' ' . $this->month->year . ' महिनाको Petrol Bill अझै दर्ता गर्नुभएको छैन। ' . adToBs($this->deadline->format('Y-m-d')) . ' (BS) भित्र दर्ता गर्नुहोस्।',
            'url'     => route('petrol.bills.create'),
            'petrol_month_id' => $this->month->id,
        ];
    }
}
