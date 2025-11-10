<?php

namespace App\Observers;

use App\Models\Communication;
use App\Jobs\ProcessCommunicationReceipt;
use Carbon\Carbon;

class CommunicationObserver
{
    public function created(Communication $communication)
    {
        // If the communication is scheduled, we'll let the scheduler handle it
        if ($communication->isScheduled()) {
            return;
        }

        // For immediate sending, dispatch the job
        $this->dispatchProcessingJob($communication);
    }

    public function updated(Communication $communication)
    {
        // If status changed to approved and it's not already processed
        if ($communication->isDirty('status') && 
            $communication->isApproved() && 
            !$communication->isProcessed()) {
            
            if ($communication->isScheduled()) {
                // Schedule the job for later
                $this->scheduleProcessingJob($communication);
            } else {
                // Process immediately
                $this->dispatchProcessingJob($communication);
            }
        }
    }

    protected function dispatchProcessingJob(Communication $communication)
    {
        ProcessCommunicationReceipt::dispatch($communication)
            ->onQueue('communications');
    }

    protected function scheduleProcessingJob(Communication $communication)
    {
        $delay = $communication->scheduled_for->diffInSeconds(now());
        
        ProcessCommunicationReceipt::dispatch($communication)
            ->delay(now()->addSeconds($delay))
            ->onQueue('communications');
    }
}