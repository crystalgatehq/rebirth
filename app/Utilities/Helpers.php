<?php

use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;

if (! function_exists('phoneNumberPrefix')) {
    /**
     * Format a phone number with country code
     *
     * @param string $telephone The phone number to format
     * @param string $code The country code (default: 'KE' for Kenya)
     * @param int $length The length of the phone number to keep (default: -9, keeps last 9 digits)
     * @return string Formatted phone number in E.164 format
     */
    function phoneNumberPrefix(string $telephone, string $code = 'KE', int $length = -9): string
    {
        $number = substr($telephone, $length);
        $phoneUtil = PhoneNumberUtil::getInstance();

        return $phoneUtil->format($phoneUtil->parse($number, $code), PhoneNumberFormat::E164);
    }
}