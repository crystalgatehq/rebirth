<?php

namespace App\Services\IAN;

use App\Models\Profile;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use App\Models\OutboundTextMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AfricaIsTalkingServices
{
    /**
     * Check if we're currently running migrations
     */
    protected function isRunningMigrations(): bool
    {
        return app()->runningInConsole() && 
               in_array('migrate', $_SERVER['argv'] ?? []) &&
               !in_array('--pretend', $_SERVER['argv'] ?? []);
    }
    protected $username;
    protected $apiKey;
    protected $baseUrl = 'https://api.africastalking.com/version1/messaging';
    protected $sandboxUrl = 'https://api.sandbox.africastalking.com/version1/messaging';
    protected $provider = 'africastalking';
    protected $senderId;

    // Africa's Talking status codes
    protected const STATUS_CODES = [
        // Success codes
        100 => 'PROCESSED',  // Message is being processed
        101 => 'SENT',       // Message is sent to the provider
        102 => 'QUEUED',     // Message is queued for delivery
        
        // Error codes
        401 => 'RISK_HOLD',              // Message held due to risk
        402 => 'INVALID_SENDER_ID',      // Invalid sender ID
        403 => 'INVALID_PHONE_NUMBER',   // Invalid phone number
        404 => 'UNSUPPORTED_NUMBER_TYPE',// Number not supported
        405 => 'INSUFFICIENT_BALANCE',   // Not enough balance
        406 => 'USER_IN_BLACKLIST',      // Number is blacklisted
        407 => 'COULD_NOT_ROUTE',        // Could not find route to number
        408 => 'INVALID_MESSAGE',        // Message is invalid
        409 => 'DND_REJECTION',          // Do Not Disturb rejection
        410 => 'MESSAGE_EXPIRED',        // Message expired before delivery
        411 => 'MESSAGE_TOO_LONG',       // Message too long
        412 => 'MESSAGE_NOT_SENT',       // Generic failure
        413 => 'MESSAGE_REJECTED',       // Message rejected
        414 => 'MESSAGE_QUEUE_FULL',     // Message queue is full
        415 => 'MESSAGE_TOO_OLD',        // Message too old to send
        500 => 'INTERNAL_SERVER_ERROR',  // Internal server error
        501 => 'GATEWAY_ERROR',          // Gateway error
        502 => 'REJECTED_BY_GATEWAY',    // Rejected by gateway
    ];

    protected $settings = [];

    public function __construct()
    {
        if ($this->isRunningMigrations()) {
            $this->setMigrationDefaults();
            return;
        }

        try {
            $this->settings = $this->loadSettings();
            
            // Only try to get settings if we're not in a migration context
            if (!$this->isRunningMigrations()) {
                $this->username = $this->getSetting('AFRICASTALKING_USERNAME');
                $this->apiKey = $this->getSetting('AFRICASTALKING_API_KEY');
                $this->senderId = $this->getSetting('AFRICASTALKING_SENDER_ID', config('app.name', 'Rebirth'));
            } else {
                $this->setMigrationDefaults();
            }
        } catch (\Exception $e) {
            // If we can't load settings, use migration defaults
            $this->setMigrationDefaults();
        }
    }

    /**
     * Load all settings from the database at once
     */
    /**
     * Set default values for migration context
     */
    protected function setMigrationDefaults(): void
    {
        $this->username = 'migration';
        $this->apiKey = 'migration';
        $this->senderId = 'REBIRTH';
    }

    /**
     * Load settings from the database
     */
    protected function loadSettings(): array
    {
        if ($this->isRunningMigrations()) {
            return [
                'AFRICASTALKING_USERNAME' => 'migration',
                'AFRICASTALKING_API_KEY' => 'migration',
                'AFRICASTALKING_SENDER_ID' => 'REBIRTH',
            ];
        }

        try {
            // Use DB facade directly to avoid model issues
            if (!\Schema::hasTable('settings')) {
                return [];
            }

            return \DB::table('settings')
                ->whereIn('name', [
                    'AFRICASTALKING_USERNAME',
                    'AFRICASTALKING_API_KEY',
                    'AFRICASTALKING_SENDER_ID'
                ])
                ->pluck('current_value', 'name')
                ->toArray();
        } catch (\Exception $e) {
            // If we can't load settings, return empty array
            return [];
        }
    }

    /**
     * Get a setting value by key with an optional default
     */
    protected function getSetting(string $key, $default = null)
    {
        if ($this->isRunningMigrations()) {
            return $default ?? 'migration';
        }

        $value = $this->settings[$key] ?? $default;
        
        // Only throw exception if we're not in a migration context
        if ($value === null && !$this->isRunningMigrations()) {
            $this->missingSetting($key);
        }
        
        return $value ?? $default;
    }
    
    /**
     * Throw an exception for a missing required setting
     */
    protected function missingSetting($key)
    {
        throw new \RuntimeException("Missing required setting: {$key}");
    }

    /**
     * Send an SMS message
     *
     * @param string $to Recipient phone number(s), comma-separated for multiple
     * @param string $message Message content
     * @param string|null $from Sender ID (optional)
     * @param array $options Additional options
     * @return array
     */
    public function send(string $to, string $message, ?string $from = null, array $options = []): array
    {
        // Skip sending during migrations
        if ($this->username === 'migration' && $this->apiKey === 'migration') {
            return [
                'status' => 'success',
                'message' => 'Skipped during migration',
                'data' => [
                    'SMSMessageData' => [
                        'Message' => 'Sent to 1/1 Total Cost: KES 0.0000',
                        'Recipients' => [
                            [
                                'statusCode' => 101,
                                'number' => $to,
                                'status' => 'Success',
                                'cost' => 'KES 0.0000',
                                'messageId' => 'ATXID_migration_' . uniqid(),
                                'messageParts' => 1
                            ]
                        ]
                    ]
                ]
            ];
        }

        try {
            $payload = array_merge([
                'username' => $this->username,
                'to' => $to,
                'message' => $message,
                'from' => $from,
                'bulkSMSMode' => $options['bulkSMSMode'] ?? 1,
                'enqueue' => $options['enqueue'] ?? 1,
                'keyword' => $options['keyword'] ?? null,
                'linkId' => $options['linkId'] ?? null,
                'retryDurationInHours' => $options['retryDurationInHours'] ?? null,
            ], $options);

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'apiKey' => $this->apiKey,
            ])->post($this->getBaseUrl(), array_filter([
                'username' => $this->username,
                'to' => $this->formatPhoneNumber($to),
                'message' => $message,
                'from' => $from ?? $this->senderId,
                'bulkSMSMode' => $options['bulkSMSMode'] ?? 1,
                'enqueue' => $options['enqueue'] ?? 1,
                'keyword' => $options['keyword'] ?? null,
                'linkId' => $options['linkId'] ?? null,
                'retryDurationInHours' => $options['retryDurationInHours'] ?? null,
            ]));

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['SMSMessageData']['Recipients'])) {
                $recipients = $responseData['SMSMessageData']['Recipients'];
                $results = [];
                
                foreach ($recipients as $recipient) {
                    $statusCode = (int)($recipient['statusCode'] ?? 0);
                    $status = $this->getStatusFromCode($statusCode);
                    
                    $results[] = [
                        'success' => $statusCode >= 100 && $statusCode < 300,
                        'provider' => $this->provider,
                        'message_id' => $recipient['messageId'] ?? null,
                        'status' => $status,
                        'status_code' => $statusCode,
                        'number' => $recipient['number'] ?? $to,
                        'cost' => $recipient['cost'] ?? null,
                        'response' => $recipient,
                    ];
                }
                
                return [
                    'success' => true,
                    'message' => $responseData['SMSMessageData']['Message'] ?? 'Message sent',
                    'recipients' => $results,
                    'raw_response' => $responseData,
                ];
            }

            $error = $responseData['SMSMessageData']['Message'] ?? 'Unknown error';
            throw new \Exception($error);

        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'to' => $to,
                'error' => $e->getMessage(),
                'provider' => $this->provider,
            ]);

            return [
                'success' => false,
                'provider' => $this->provider,
                'error' => $e->getMessage(),
                'status' => 'FAILED',
                'status_code' => 500,
            ];
        }
    }

    /**
     * Get the status of a message
     *
     * @param string $messageId The message ID to check
     * @return array
     */
    public function getMessageStatus(string $messageId): array
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'apiKey' => $this->apiKey,
            ])->get($this->getBaseUrl(), [
                'username' => $this->username,
                'messageId' => $messageId,
            ]);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['SMSMessageData']['Recipients'][0])) {
                $recipient = $responseData['SMSMessageData']['Recipients'][0];
                $statusCode = (int)($recipient['statusCode'] ?? 0);
                $status = $this->getStatusFromCode($statusCode);
                
                return [
                    'success' => $statusCode >= 100 && $statusCode < 300,
                    'status' => $status,
                    'status_code' => $statusCode,
                    'number' => $recipient['number'] ?? null,
                    'cost' => $recipient['cost'] ?? null,
                    'message_id' => $recipient['messageId'] ?? $messageId,
                    'provider' => $this->provider,
                    'response' => $recipient,
                ];
            }

            throw new \Exception($responseData['SMSMessageData']['Message'] ?? 'Unknown error');

        } catch (\Exception $e) {
            Log::error('Failed to get SMS status', [
                'message_id' => $messageId,
                'error' => $e->getMessage(),
                'provider' => $this->provider,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'UNKNOWN',
                'status_code' => 500,
            ];
        }
    }

    /**
     * Get status text from status code
     */
    protected function getStatusFromCode(int $statusCode): string
    {
        return self::STATUS_CODES[$statusCode] ?? 'UNKNOWN';
    }

    /**
     * Get base URL based on environment
     */
    protected function getBaseUrl(): string
    {
        return app()->environment('production') ? $this->baseUrl : $this->sandboxUrl;
    }

    /**
     * Format phone number for Africa's Talking
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Format phone number for Africa's Talking
        $phone = preg_replace('/\D/', '', $phone);
        
        // Add country code if missing
        if (strlen($phone) === 9 && !str_starts_with($phone, '+')) {
            $phone = '254' . $phone;
        }
        
        return '+' . ltrim($phone, '+');
    }

    /**
     * Map status code to application's status constant for CommunicationReceipt
     */
    public function mapStatusToCommunicationReceiptStatus(int $statusCode): string
    {
        return match($statusCode) {
            // Success codes - message sent to provider
            100, 101, 102 => 'SENT', // Processed, Sent, Queued
            
            // Delivery status codes
            103 => 'DELIVERED', // Delivered (if this code exists)
            
            // Error codes - message failed to send
            401, 402, 403, 404, 405, 406, 407, 408, 409, 410, 411, 412, 413, 414, 415 => 'FAILED',
            500, 501, 502 => 'FAILED', // Server/gateway errors
            
            // Default to pending for any other status
            default => 'PENDING',
        };
    }

    /**
     * Legacy send method for backward compatibility
     *
     * @deprecated Use the new send() method instead
     */
    public function sendLegacy(string $recipient, string $content): array|null
    {
        $response = $this->send($recipient, $content, getSetting('AFRICAS_TALKING_SENDER_ID'));
        
        if ($response['success']) {
            return array_filter([
                'transaction_amount' => ($response['cost'] ?? 0) + 0.2,
                'transaction_id' => $response['recipients'][0]['message_id'] ?? null,
                '_status' => $response['recipients'][0]['status'] ?? 'PENDING',
            ]);
        }
        
        return null;
    }

    public function accountBalance(): array|null
    {
        // Initialize Africa's Talking service
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
            'apiKey' => getSetting('AFRICAS_TALKING_API_KEY'),
        ])->get(getSetting('AFRICAS_TALKING_USER_ENDPOINT'), [
            'username' => getSetting('AFRICAS_TALKING_USERNAME'),
        ]);

        if ($response->successful()) {
            $data = collect($response->json())->flatten();

            return [
                'account_service' => get_class($this),
                'last_checked_at' => Carbon::parse(REQUEST_TIMESTAMP)->toDateTimeString(),
                'account_balance' => optional($data)[0] ? getOnlyNumbers(trim($data[0])) : 0,
            ];
        }
    }

    public function deliveryReports(array $request): void
    {
        // Retrieve the text message using the transaction ID from the request
        $textMessage = OutboundTextMessage::where('transaction_id', trim($request['id']))->firstOrFail();

        // Determine the status to update based on the presence of a failure reason
        $status = isset($request['failureReason'])
            ? OutboundTextMessage::getStatusValueByLabel(trim($request['failureReason']))
            : OutboundTextMessage::getStatusValueByLabel(trim($request['status']));

        // Update the text message with the provided network code, failure reason, and status
        $textMessage->update([
            'network_code' => $request['networkCode'] ?? NULL,
            'failure_reason' => $request['failureReason'] ?? NULL,
            '_status' => $status
        ]);
    }

    public function bulkSMSOptOut(array $request): void
    {
        // Retrieve the farmer of the person who has opted out.
        $phoneNumber = phoneNumberPrefix(trim($request['phoneNumber']));
        $farmer = Farmer::where('phone', $phoneNumber)->firstOrFail();

        // Get the existing configuration and update the sms field.
        $configuration = json_decode($farmer->configuration, true);

        // If the configuration is not an array, initialize it as an empty array.
        if (!is_array($configuration)) {
            $configuration = [];
        }

        // Update the sms field.
        $configuration['sms'] = false;

        // Save the updated configuration back to the farmer.
        $farmer->update(['configuration' => json_encode($configuration)]);
    }
}
