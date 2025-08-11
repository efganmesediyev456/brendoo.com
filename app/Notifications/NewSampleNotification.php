<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use YieldStudio\LaravelExpoNotifier\ExpoNotificationsChannel;
use YieldStudio\LaravelExpoNotifier\Dto\ExpoMessage;

class NewSampleNotification extends Notification
{
    public $title;
    public $body;

    public function __construct($title, $body){
        $this->title = $title;
        $this->body = $body;
    }
    public function via($notifiable)
    {
        return [ExpoNotificationsChannel::class];
    }

    public function toExpoNotification($notifiable)
    {
        return (new ExpoMessage())
            ->to([$notifiable->expo_token]) 
            ->title($this->title)
            ->body($this->body)
            ->channelId('ATMQ4pAAZLkE3_Mg-lU6H3roN-QxNq6xh-lO6N4g');
    }
}
