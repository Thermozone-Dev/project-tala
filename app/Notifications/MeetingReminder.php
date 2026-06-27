<?php

namespace App\Notifications;

use App\Models\Meeting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MeetingReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Meeting $meeting,
        public string $type // 'daily' or '30min'
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $when = $this->type === 'daily'
            ? 'tomorrow'
            : 'in 30 minutes';

        return (new MailMessage)
            ->subject("Reminder: {$this->meeting->title}")
            ->line("You have a meeting {$when}.")
            ->line("Title: {$this->meeting->title}")
            ->line("Scheduled At: {$this->meeting->scheduled_at->format('M d, Y h:i A')}")
            ->action('Join Meeting', $this->meeting->meeting_link ?? '#');
    }
}
