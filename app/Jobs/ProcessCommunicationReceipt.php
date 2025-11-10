<?php

namespace App\Jobs;

use App\Models\Communication;
use App\Models\CommunicationReceipt;
use App\Services\IAN\AfricaIsTalkingServices;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCommunicationReceipt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 600]; // 1, 5, 10 minutes

    protected $communication;

    public function __construct(Communication $communication)
    {
        $this->communication = $communication;
    }

    public function handle(AfricaIsTalkingServices $africaIsTalkingServices)
    {
        // Mark communication as processing
        $this->communication->markAsProcessing();

        try {
            // Process each recipient in the contact group
            foreach ($this->communication->contactGroup->farmers as $farmer) {
                // Create receipt
                $receipt = CommunicationReceipt::create([
                    'communication_id' => $this->communication->id,
                    'contact_group_id' => $this->communication->contact_group_id,
                    'communication_category_id' => $this->communication->communication_category_id,
                    'campaign_id' => $this->communication->campaign_id,
                    'phone_number' => $farmer->phone,
                    'recipient_name' => $farmer->name,
                    'message' => $this->communication->content,
                    'template_identifier' => $this->communication->template_identifier,
                    'template_variables' => $this->communication->variables,
                    'sender_id' => $this->communication->sender_id,
                    'status' => CommunicationReceipt::STATUS_PROCESSING,
                    'scheduled_at' => $this->communication->scheduled_for,
                    'created_by' => $this->communication->created_by,
                    'team_id' => $this->communication->team_id,
                ]);

                // Send the SMS
                $response = $africaIsTalkingServices->send(
                    $farmer->phone,
                    $this->communication->content,
                    $this->communication->sender_id
                );

                // Update receipt with provider response
                $receipt->update([
                    'provider_name' => $response['provider'],
                    'provider_message_id' => $response['recipients'][0]['message_id'] ?? null,
                    'provider_response' => $response,
                    'status' => $response['success']
                        ? CommunicationReceipt::STATUS_SENT
                        : CommunicationReceipt::STATUS_FAILED,
                    'sent_at' => now(),
                    'error_message' => $response['error'] ?? null,
                ]);
            }

            // Update communication status
            $this->communication->markAsProcessed();

        } catch (\Exception $e) {
            $this->communication->markAsFailed($e->getMessage());
            throw $e; // Let the job retry if needed
        }
    }

    public function failed(\Throwable $exception)
    {
        $this->communication->markAsFailed($exception->getMessage());
    }
}