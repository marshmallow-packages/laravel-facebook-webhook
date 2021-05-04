<?php

namespace Marshmallow\LaravelFacebookWebhook\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Marshmallow\LaravelFacebookWebhook\Models\WebhookLeadResponse;

class FacebookWebhookReceived
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $webhookdata;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(WebhookLeadResponse $webhookLeadResponse)
    {
        $this->webhookdata = $webhookLeadResponse;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('laravel-facebook-webhook');
    }
}
