<?php

namespace App\Notifications\Channels;

use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Twilio\Exceptions\RestException;
use Twilio\Rest\Client;

class TwilioSmsChannel
{
    public function send($notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toTwilioSms')) {
            return;
        }

        $to = $notifiable->routeNotificationFor('twilioSms', $notification)
            ?? (method_exists($notifiable, 'routeNotificationForTwilioSms') ? $notifiable->routeNotificationForTwilioSms($notification) : null);

        $to = is_string($to) ? trim($to) : '';
        if ($to === '') {
            return;
        }

        $body = (string) $notification->toTwilioSms($notifiable);
        $body = trim($body);
        if ($body === '') {
            return;
        }

        $sid = (string) config('services.twilio.sid');
        $token = (string) config('services.twilio.token');
        $from = (string) config('services.twilio.from');

        if ($sid === '' || $token === '' || $from === '') {
            return;
        }

        try {
            $client = new Client($sid, $token);
            $client->messages->create($to, [
                'from' => $from,
                'body' => $body,
            ]);
        } catch (RestException $e) {
            // Never break attendance flow due to SMS failures.
            Log::warning('Twilio SMS send failed', [
                'to' => $to,
                'from' => $from,
                'notification' => get_class($notification),
                'status' => $e->getStatusCode(),
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Twilio SMS send crashed', [
                'to' => $to,
                'from' => $from,
                'notification' => get_class($notification),
                'message' => $e->getMessage(),
            ]);
        }
    }
}

