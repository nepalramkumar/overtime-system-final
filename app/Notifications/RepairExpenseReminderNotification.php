<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class RepairExpenseReminderNotification extends Notification
{
    use Queueable;

    protected $fyYear;
    protected $limit;
    protected $claimed;
    protected $remaining;

    public function __construct(string $fyYear, float $limit, float $claimed, float $remaining)
    {
        $this->fyYear = $fyYear;
        $this->limit = $limit;
        $this->claimed = $claimed;
        $this->remaining = $remaining;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Repair Expense Claim बाँकी: FY ' . $this->fyYear)
            ->greeting('नमस्ते ' . ($notifiable->name ?? '') . ',')
            ->line('चालु आर्थिक वर्ष (FY ' . $this->fyYear . ') सकिन लाग्दैछ (Ashad महिना सुरु भयो)।')
            ->line('तपाईंको कुल Repair Expense Limit रु. ' . number_format($this->limit) . ' मध्ये रु. ' . number_format($this->claimed) . ' मात्र claim गर्नुभएको छ।')
            ->line('बाँकी रु. ' . number_format($this->remaining) . ' यो FY (Ashad महिना नसकिँदै) claim गर्न मिल्छ, नत्र यो FY पछि यो limit प्रयोग गर्न पाइने छैन।')
            ->action('Repair Expense दर्ता गर्नुहोस्', route('repair.expenses.create'));
    }

    public function toArray($notifiable): array
    {
        return [
            'title'   => 'Repair Expense Claim बाँकी',
            'message' => 'FY ' . $this->fyYear . ' मा रु. ' . number_format($this->remaining) . ' Repair Expense claim गर्न बाँकी छ (Limit रु. ' . number_format($this->limit) . ' मध्ये रु. ' . number_format($this->claimed) . ' claim भइसक्यो)।',
            'url'     => route('repair.expenses.create'),
            'fy_year' => $this->fyYear,
        ];
    }
}
