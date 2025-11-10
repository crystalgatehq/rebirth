<?php

namespace App\Channels\Messages;

class SmsMessage
{
    /**
     * The phone number the message should be sent to.
     */
    public $to;

    /**
     * The phone number the message should be sent from.
     */
    public $from;

    /**
     * The message content.
     */
    public $content;

    /**
     * Additional options for the message.
     */
    public $options = [];

    /**
     * Set the message recipient.
     */
    public function to($to)
    {
        $this->to = $to;
        return $this;
    }

    /**
     * Set the message sender.
     */
    public function from($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * Set the message content.
     */
    public function content($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Set additional options for the message.
     */
    public function options(array $options)
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }
}
