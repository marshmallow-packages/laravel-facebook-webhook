<?php

namespace Marshmallow\LaravelFacebookWebhook\Jobs;

use Exception;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Client\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob as SpatieProcessWebhookJob;
use Marshmallow\LaravelFacebookWebhook\Controllers\FacebookTokenController;

class ProcessFacebookLeadWebhookJob extends SpatieProcessWebhookJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (
            !isset($this->webhookCall->payload['object']) ||
            $this->webhookCall->payload['object'] !== 'page'
        ) {
            throw new \Exception('Invalid object type');
        }
        $webhookResponses   = $this->webhookCall->payload['entry'][0]['changes'];
        $tokenController    = new FacebookTokenController();

        foreach ($webhookResponses as $webhookResponse) {
            $tokenController->storeWebhookResponse($webhookResponse, $this->webhookCall['name']);
        }

        // $this->webhookCall->delete();

        return;
    }
}
