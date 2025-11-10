<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class OtpNotification extends SmsNotification
{
    use Queueable;

    /**
     * The OTP code to be sent.
     */
    public $otp;

    /**
     * The message template.
     */
    protected $template = 'Your OTP code is: :otp. Valid for :minutes minutes.';

    /**
     * Create a new notification instance.
     */
    public function __construct(string $otp, int $minutes = 5, string $template = null)
    {
        $this->otp = $otp;
        $this->template = $template ?? $this->template;
        
        parent::__construct(function() use ($minutes) {
            return str_replace(
                [':otp', ':minutes'],
                [$this->otp, $minutes],
                $this->template
            );
        });
    }
}
