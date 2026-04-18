<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;
use App\Notifications\Channels\TwilioSmsChannel;

class AbsenceAlertNotification extends Notification
{
    use Queueable;

    public string $date;
    public ?string $expectedStart;
    public string $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $date, ?string $expectedStart, string $message)
    {
        $this->date = $date;
        $this->expectedStart = $expectedStart;
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
            ->subject('Absence Alert')
            ->greeting('Hello ' . ($notifiable->name ?? ''))
            ->line('No attendance record was found for ' . $prettyDate . ' by the cutoff time.')
            ->when($this->expectedStart, fn (MailMessage $m) => $m->line('Expected start: ' . $this->expectedStart))
            ->line($this->message)
            ->action('View Dashboard', route('employee.dashboard'));
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
            'title' => 'Absence alert',
            'body' => 'No time-in recorded by cutoff for ' . $this->date . '.',
            'date' => $this->date,
            'expected_start' => $this->expectedStart,
            'severity' => 'danger',
            'url' => route('employee.dashboard'),
        ];
    }

    public function toTwilioSms($notifiable): string
    {
        $extra = $this->expectedStart ? (' Expected start: ' . $this->expectedStart . '.') : '';
        return 'Absence alert: No time-in recorded by cutoff for ' . $this->date . '.' . $extra;
    }
}
