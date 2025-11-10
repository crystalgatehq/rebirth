<?php

namespace App\Services\OTP;

use Exception;
use InvalidArgumentException;
use App\Jobs\SendOtpNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OneTimePasswordServices
{
    public const TTL = 300; // 5 minutes
    public const RATE_LIMIT = 60; // 1 minute
    public const ATTEMPTS_LIMIT = 5; // Max attempts
    public const ATTEMPTS_RESET_HOURS = 24; // Hours before attempts counter resets
    public const OTP_LENGTH = 6;

    /**
     * Send OTP to the given telephone number
     *
     * @param string $telephone
     * @return bool
     * @throws Exception
     */
    public function send(string $telephone): bool
    {
        $this->validateTelephone($telephone);
        $this->checkRateLimit($telephone);

        $otp = $this->generateOtp();
        $this->storeOtp($telephone, $otp);
        $this->setRateLimit($telephone);
        $this->dispatchOtpNotification($telephone, $otp);
        
        return true;
    }

    /**
     * Verify the provided OTP for the given identifier
     *
     * @param string $identifier
     * @param string $otp
     * @return bool
     * @throws Exception
     */
    public function verify(string $identifier, string $otp): bool
    {
        $cachedOtp = $this->getCachedOtp($identifier);
        
        if (!$cachedOtp) {
            return false;
        }

        $this->checkAttemptsLimit($identifier);

        if (!hash_equals((string)$cachedOtp['token'], (string)$otp)) {
            $this->incrementAttempts($identifier);
            return false;
        }

        $this->invalidateOtp($identifier);
        return true;
    }

    /**
     * Get remaining verification attempts
     */
    public function getRemainingAttempts(string $identifier): int
    {
        return max(0, self::ATTEMPTS_LIMIT - (int)Cache::get($this->getAttemptsKey($identifier), 0));
    }

    /**
     * Get remaining time in seconds before OTP expires
     */
    public function getRemainingTime(string $identifier): ?int
    {
        $expiration = Cache::get($this->getExpirationKey($identifier));
        return $expiration ? max(0, $expiration - time()) : null;
    }

    /**
     * Generate a new OTP
     */
    protected function generateOtp(): string
    {
        return str_pad((string)random_int(0, 10 ** self::OTP_LENGTH - 1), self::OTP_LENGTH, '0', STR_PAD_LEFT);
    }

    /**
     * Store OTP in cache with expiration
     */
    protected function storeOtp(string $identifier, string $otp): void
    {
        $key = $this->getCacheKey($identifier);
        $expiresAt = now()->addSeconds(self::TTL);
        
        Cache::put($key, [
            'token' => $otp,
            'created_at' => now()->timestamp,
        ], $expiresAt);
        
        Cache::put($this->getExpirationKey($identifier), $expiresAt->timestamp, $expiresAt);
    }

    /**
     * Get cached OTP data
     */
    protected function getCachedOtp(string $identifier): ?array
    {
        return Cache::get($this->getCacheKey($identifier));
    }

    /**
     * Check and handle rate limiting
     *
     * @throws Exception
     */
    protected function checkRateLimit(string $identifier): void
    {
        if ($this->isRateLimited($identifier)) {
            $expiresAt = Cache::get($this->getRateLimitKey($identifier) . ':expiry');
            $remaining = $expiresAt ? max(1, $expiresAt - time()) : self::RATE_LIMIT;
            $waitTime = ceil($remaining / 60); // Convert to minutes
            $timeUnit = $waitTime > 1 ? 'minutes' : 'minute';
            throw new Exception("Please wait {$waitTime} {$timeUnit} before requesting another OTP.");
        }
    }

    /**
     * Check and handle maximum attempts
     *
     * @throws Exception
     */
    protected function checkAttemptsLimit(string $identifier): void
    {
        if ($this->getRemainingAttempts($identifier) <= 0) {
            $this->invalidateOtp($identifier);
            throw new Exception("You have exceeded the maximum of " . self::ATTEMPTS_LIMIT . " OTP attempts. Please try again after " . self::ATTEMPTS_RESET_HOURS . " hours.");
        }
    }

    /**
     * Validate telephone number format
     *
     * @throws InvalidArgumentException
     */
    protected function validateTelephone(string $telephone): void
    {
        if (empty($telephone)) {
            throw new InvalidArgumentException('Telephone number is required');
        }
        
        // Add additional validation if needed
        if (!preg_match('/^\+?[0-9]{10,15}$/', $telephone)) {
            throw new InvalidArgumentException('Invalid telephone number format');
        }
    }

    /**
     * Dispatch OTP notification
     */
    protected function dispatchOtpNotification(string $telephone, string $otp): void
    {
        try {
            SendOtpNotification::dispatchNow($telephone, $otp);
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch OTP notification: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function incrementAttempts(string $identifier): int
    {
        $key = $this->getAttemptsKey($identifier);
        return (int)Cache::increment($key, 1, now()->addDay());
    }

    protected function isRateLimited(string $identifier): bool
    {
        return Cache::has($this->getRateLimitKey($identifier));
    }

    protected function setRateLimit(string $identifier): void
    {
        $expiresAt = now()->addSeconds(self::RATE_LIMIT);
        Cache::put($this->getRateLimitKey($identifier), true, $expiresAt);
        Cache::put($this->getRateLimitKey($identifier) . ':expiry', $expiresAt->timestamp, $expiresAt);
    }

    /**
     * Invalidate and clear the OTP for the given identifier
     * This clears all related cache entries for the OTP
     */
    protected function invalidateOtp(string $identifier): void
    {
        $key = $this->getCacheKey($identifier);
        Cache::forget($key);
        Cache::forget($this->getAttemptsKey($identifier));
        Cache::forget($this->getExpirationKey($identifier));
    }

    /**
     * Clear the OTP for the given identifier
     * This is an alias for invalidateOtp for better code readability
     */
    public function clearOtp(string $identifier): void
    {
        $this->invalidateOtp($identifier);
    }

    protected function getCacheKey(string $identifier): string
    {
        return 'otp:' . md5($identifier);
    }

    protected function getRateLimitKey(string $identifier): string
    {
        return 'otp:rate_limit:' . md5($identifier);
    }

    protected function getAttemptsKey(string $identifier): string
    {
        return 'otp:attempts:' . md5($identifier);
    }

    protected function getExpirationKey(string $identifier): string
    {
        return 'otp:expiration:' . md5($identifier);
    }
}
