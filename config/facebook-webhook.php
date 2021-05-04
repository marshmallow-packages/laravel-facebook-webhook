<?php

return [
    'configs' => [
        [
            'name' => 'facebook-lead',
            'signing_secret' => env('FACEBOOK_APP_SECRET'),
            'signature_header_name' => 'X-Hub-Signature',
            'signature_validator' => \Marshmallow\LaravelFacebookWebhook\SignatureValidator\FacebookSignatureValidator::class,
            'webhook_profile' => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,
            'webhook_response' => \Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,
            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,
            'process_webhook_job' => \Marshmallow\LaravelFacebookWebhook\Jobs\ProcessFacebookLeadWebhookJob::class,
            'callback_route' => env('FACEBOOK_CALLBACK_ROUTE', 'webhook-client-facebook-lead'),
            'graph_api_version' => 'v10.0',
            'app_id' => env('FACEBOOK_APP_ID'),
            'app_secret' => env('FACEBOOK_APP_SECRET'),
            'page_id' => env('FACEBOOK_PAGE_ID'),
        ],
    ],
];
