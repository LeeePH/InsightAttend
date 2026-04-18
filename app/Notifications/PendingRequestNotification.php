<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;
use App\Notifications\Channels\TwilioSmsChannel;

class PendingRequestNotification extends Notification
{
    use Queueable;

    public string $requestType;
    public int $requestId;
    public string $employeeName;
    public string $submittedAt;
    public string $adminUrl;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $requestType, int $requestId, string $employeeName, string $submittedAt, string $adminUrl)
    {
        $this->requestType = $requestType;
        $this->requestId = $requestId;
        $this->employeeName = $employeeName;
        $this->submittedAt = $submittedAt;
        $this->adminUrl = $adminUrl;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $channels = array_values(array_unique(array_filter(config('attendance.notification_channels', ['database']))));
        $out = [];
        foreach ($channels as $ch) {
            if ($ch === 'sms') $out[] = TwilioSmsChannel::class;
            else $out[] = $ch;
        }
        return $out;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $pretty = Carbon::parse($this->submittedAt)->format('M j, Y g:i A');
        return (new MailMessage)
            ->subject('Pending ' . ucfirst($this->requestType) . ' Request')
            ->greeting('Hello ' . ($notifiable->name ?? ''))
            ->line('A new ' . $this->requestType . ' request is pending review.')
            ->line('Employee: ' . $this->employeeName)
            ->line('Submitted: ' . $pretty)
            ->action('Review Request', $this->adminUrl);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title' => 'Pending ' . $this->requestType . ' request',
            'body' => $this->employeeName . ' submitted a ' . $this->requestType . ' request.',
            'request_type' => $this->requestType,
            'request_id' => $this->requestId,
            'employee_name' => $this->employeeName,
            'submitted_at' => $this->submittedAt,
            'severity' => 'info',
            'url' => $this->adminUrl,
        ];
    }

    public function toTwilioSms($notifiable): string
    {
        return 'Pending ' . $this->requestType . ' request from ' . $this->employeeName . '.';
    }
}
