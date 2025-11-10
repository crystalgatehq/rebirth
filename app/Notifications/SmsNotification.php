<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Channels\Messages\SmsMessage;

class SmsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The message content or callback.
     */
    protected $content;

    /**
     * The phone number to send to (optional, can be set via to() method)
     */
    protected $to;

    /**
     * The sender ID (optional, can be set via from() method)
     */
    protected $from;

    /**
     * Additional options for the message.
     */
    protected $options = [];

    /**
     * Create a new notification instance.
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * Set the recipient's phone number.
     */
    public function to($phone)
    {
        $this->to = $phone;
        return $this;
    }

    /**
     * Set the sender ID.
     */
    public function from($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * Set additional options for the message.
     */
    public function options(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['sms'];
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms($notifiable = null)
    {
        $message = new SmsMessage();
        
        // Set the content (can be a string or a callable)
        $content = is_callable($this->content) 
            ? call_user_func($this->content, $notifiable)
            : $this->content;
            
        $message->content($content);
        
        // Set the sender if provided
        if ($this->from) {
            $message->from($this->from);
        }
        
        // Set any additional options
        if (!empty($this->options)) {
            $message->options($this->options);
        }
        
        return $message;
    }
    
    /**
     * Get the phone number the notification should be sent to.
     */
    public function routeNotificationForSms($notifiable)
    {
        // If a specific phone number was set, use it
        if ($this->to) {
            return $this->to;
        }
        
        // Otherwise try to get the phone number from the notifiable
        if (is_object($notifiable)) {
            if (method_exists($notifiable, 'routeNotificationForSms')) {
                return $notifiable->routeNotificationForSms($this);
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

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            'content' => $this->content,
        ];
    }
}
