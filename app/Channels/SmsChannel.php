<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use App\Services\IAN\AfricaIsTalkingServices;
use App\Channels\Messages\SmsMessage;

class SmsChannel
{
    /**
     * The Africa's Talking service instance.
     */
    protected $africasTalking;

    /**
     * Create a new SMS channel instance.
     */
    public function __construct(AfricaIsTalkingServices $africasTalking)
    {
        $this->africasTalking = $africasTalking;
    }

    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification)
    {
        // Get the message from the notification
        $message = $notification->toSms($notifiable);

        // If it's a string, convert it to a SmsMessage
        if (is_string($message)) {
            $message = (new SmsMessage())->content($message);
        }

        // Get the recipient's phone number
        $to = $this->getRecipientPhone($notifiable, $notification);
        
        if (empty($to)) {
            throw new \InvalidArgumentException('No recipient phone number provided');
        }

        // If no 'from' is set, use the default from the config
        $from = $message->from ?? config('services.africastalking.sender_id');

        return $this->africasTalking->send(
            $to,
            $message->content,
            $from
        );
    }

    /**
     * Get the recipient's phone number from the notifiable object.
     */
    protected function getRecipientPhone($notifiable, $notification): ?string
    {
        // If the notification has a custom recipient method, use it
        if (method_exists($notification, 'routeNotificationForSms')) {
            return $notification->routeNotificationForSms($notifiable);
        }
        
        // If the notifiable is an array or has a 'phone' property
        if (is_array($notifiable) || $notifiable instanceof \ArrayAccess) {
            return $notifiable['phone'] ?? null;
        }
        
        // If the notifiable is an object with a phone property or method
        if (is_object($notifiable)) {
            if (method_exists($notifiable, 'routeNotificationForSms')) {
                return $notifiable->routeNotificationForSms($notification);
            }
            
            if (property_exists($notifiable, 'phone')) {
                return $notifiable->phone;
            }
            
            if (method_exists($notifiable, 'getPhoneNumber')) {
                return $notifiable->getPhoneNumber();
            }
        }
        
        return null;
    }
    }
}
