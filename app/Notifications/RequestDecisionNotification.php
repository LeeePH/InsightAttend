<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;
use App\Notifications\Channels\TwilioSmsChannel;

class RequestDecisionNotification extends Notification
{
    use Queueable;

    public string $requestType;
    public int $requestId;
    public string $decision; // approved|rejected
    public string $remarks;
    public string $reviewedAt;
    public string $employeeUrl;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $requestType, int $requestId, string $decision, string $remarks, string $reviewedAt, string $employeeUrl)
    {
        $this->requestType = $requestType;
        $this->requestId = $requestId;
        $this->decision = $decision;
        $this->remarks = $remarks;
        $this->reviewedAt = $reviewedAt;
        $this->employeeUrl = $employeeUrl;
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
        $pretty = Carbon::parse($this->reviewedAt)->format('M j, Y g:i A');
        return (new MailMessage)
            ->subject(ucfirst($this->requestType) . ' Request ' . ucfirst($this->decision))
            ->greeting('Hello ' . ($notifiable->name ?? ''))
            ->line('Your ' . $this->requestType . ' request has been ' . $this->decision . '.')
            ->line('Reviewed: ' . $pretty)
            ->when(trim($this->remarks) !== '', fn (MailMessage $m) => $m->line('Remarks: ' . $this->remarks))
            ->action('View', $this->employeeUrl);
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
            'title' => ucfirst($this->requestType) . ' request ' . $this->decision,
            'body' => 'Your ' . $this->requestType . ' request was ' . $this->decision . '.',
            'request_type' => $this->requestType,
            'request_id' => $this->requestId,
            'decision' => $this->decision,
            'remarks' => $this->remarks,
            'reviewed_at' => $this->reviewedAt,
            'severity' => $this->decision === 'approved' ? 'success' : 'danger',
            'url' => $this->employeeUrl,
        ];
    }

    public function toTwilioSms($notifiable): string
    {
        return ucfirst($this->requestType) . ' request ' . $this->decision . '.';
    }
}
