<?php

namespace Marshmallow\LaravelFacebookWebhook\Jobs;

use Illuminate\Bus\Queueable;
use InvalidArgumentException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Marshmallow\LaravelFacebookWebhook\Models\WebhookLeadResponse;

abstract class ProcessFacebookLeadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public WebhookLeadResponse $webhookData;

    public function __construct(WebhookLeadResponse $webhookData)
    {
        $this->webhookData = $webhookData;
    }
}
