<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;
use App\Notifications\Channels\TwilioSmsChannel;

class LateWarningNotification extends Notification
{
    use Queueable;

    public string $date;
    public string $timeIn;
    public string $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $date, string $timeIn, string $message)
    {
        $this->date = $date;
        $this->timeIn = $timeIn;
        $this->message = $message;
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
        $prettyDate = Carbon::parse($this->date)->format('M j, Y');
        return (new MailMessage)
            ->subject('Late Attendance Warning')
            ->greeting('Hello ' . ($notifiable->name ?? ''))
            ->line('You logged your time in late on ' . $prettyDate . '.')
            ->line('Time In: ' . $this->timeIn)
            ->line($this->message)
            ->action('View Dashboard', route('employee.dashboard'))
            ->line('Please ensure you arrive on time. If you believe this is incorrect, contact your administrator.');
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
            'title' => 'Late warning',
            'body' => 'You timed in late (' . $this->timeIn . ') on ' . $this->date . '.',
            'date' => $this->date,
            'time_in' => $this->timeIn,
            'severity' => 'warning',
            'url' => route('employee.dashboard'),
        ];
    }

    public function toTwilioSms($notifiable): string
    {
        return 'Late warning: You timed in at ' . $this->timeIn . ' on ' . $this->date . '.';
    }
}
