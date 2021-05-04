<?php

namespace Marshmallow\LaravelFacebookWebhook\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Marshmallow\LaravelFacebookWebhook\Models\FacebookToken;

class FacebookTokenCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $facebook_token;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(FacebookToken $facebook_token)
    {
        $this->facebook_token = $facebook_token;
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
