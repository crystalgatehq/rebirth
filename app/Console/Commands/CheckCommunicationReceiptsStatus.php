<?php

namespace App\Console\Commands;

use App\Models\CommunicationReceipt;
use App\Services\IAN\AfricaIsTalkingServices;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckCommunicationReceiptsStatus extends Command
{
    protected $signature = 'communications:check-status {--hours=24 : Check receipts updated in the last X hours}';
    protected $description = 'Check the status of pending or sent communication receipts';

    protected $africaIsTalkingServices;

    public function __construct(AfricaIsTalkingServices $africaIsTalkingServices)
    {
        parent::__construct();
        $this->africaIsTalkingServices = $africaIsTalkingServices;
    }

    public function handle()
    {
        $hours = (int) $this->option('hours');
        $cutoffTime = now()->subHours($hours);

        $query = CommunicationReceipt::query()
            ->whereIn('status', [
                CommunicationReceipt::STATUS_PENDING,
                CommunicationReceipt::STATUS_PROCESSING,
                CommunicationReceipt::STATUS_SENT,
            ])
            ->where('created_at', '>=', $cutoffTime)
            ->whereNotNull('provider_message_id');

        $total = $query->count();
        $updated = 0;

        if ($total === 0) {
            $this->info('No receipts to check.');
            return 0;
        }

        $this->info("Checking status for {$total} receipts...");

        $query->chunk(100, function ($receipts) use (&$updated) {
            foreach ($receipts as $receipt) {
                try {
                    $status = $this->africaIsTalkingServices->getMessageStatus($receipt->provider_message_id);

                    $updates = [
                        'provider_response' => array_merge(
                            (array) $receipt->provider_response,
                            ['status_check' => $status]
                        )
                    ];

                    // Update status if it has changed
                    if (isset($status['status_code'])) {
                        // Map the status code from Africa's Talking to our internal status
                        $newStatus = $this->mapProviderStatusCodeToInternalStatus($status['status_code']);
                        
                        if ($newStatus !== $receipt->status) {
                            $updates['status'] = $newStatus;
                            
                            if ($newStatus === CommunicationReceipt::STATUS_DELIVERED && !$receipt->delivered_at) {
                                $updates['delivered_at'] = now();
                            } elseif (in_array($newStatus, [CommunicationReceipt::STATUS_FAILED, CommunicationReceipt::STATUS_UNDELIVERED]) && !$receipt->failed_at) {
                                $updates['failed_at'] = now();
                                $updates['error_message'] = $status['response']['failureReason'] ?? $status['error'] ?? 'Delivery failed';
                            }
                        }
                    }

                    $receipt->update($updates);
                    $updated++;

                } catch (\Exception $e) {
                    Log::error('Failed to check status for receipt', [
                        'receipt_id' => $receipt->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });

        $this->info("Updated status for {$updated} of {$total} receipts.");
        return 0;
    }
    
    /**
     * Map the provider status code to our internal status
     */
    private function mapProviderStatusCodeToInternalStatus(int $statusCode): string
    {
        // Use the AfricaIsTalkingServices mapping method
        return $this->africaIsTalkingServices->mapStatusToCommunicationReceiptStatus($statusCode);
    }
}