<?php

return [
    'configs' => [
        [
            /*
             * This package supports multiple webhook receiving endpoints. If you only have
             * one endpoint receiving webhooks, you can use 'default'.
             */
            'name' => 'facebook-lead',

            /*
             * We expect that every webhook call will be signed using a secret. This secret
             * is used to verify that the payload has not been tampered with.
             */
            'signing_secret' => env('FACEBOOK_APP_SECRET'),

            /*
             * The name of the header containing the signature.
             */
            'signature_header_name' => 'X-Hub-Signature',

            /*
             *  This class will verify that the content of the signature header is valid.
             *
             */
            'signature_validator' => \Marshmallow\LaravelFacebookWebhook\SignatureValidator\FacebookSignatureValidator::class,

            /*
            * This class determines if the webhook call should be stored and processed.
            */
            'webhook_profile' => \Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile::class,

            /*
             * This class determines the response on a valid webhook call.
             */
            'webhook_response' => \Spatie\WebhookClient\WebhookResponse\DefaultRespondsTo::class,

            /*
             * The classname of the model to be used to store call.
             */
            'webhook_model' => \Spatie\WebhookClient\Models\WebhookCall::class,

            /*
             * The class name of the job that will process the webhook request.
             *
             * This should be set to a class that extends \Spatie\WebhookClient\ProcessWebhookJob.
             */
            'process_webhook_job' =>  \Marshmallow\LaravelFacebookWebhook\Jobs\ProcessFacebookLeadWebhookJob::class,

            /*
             * The class name of the job that will process the Facebook Lead Data.
             *
             * This should be set to a class that extends \Marshmallow\LaravelFacebookWebhook\Jobs\ProcessFacebookLeadJob,
             */
            'process_facebook_webhook_job' => '',

            /*
             * The callback route name from Facebook Leads .
             */
            'callback_route' => env('FACEBOOK_CALLBACK_ROUTE', 'webhook-client-facebook-lead'),

            /*
             * The graph api version for Facebook  .
             */
            'graph_api_version' => 'v10.0',

            /*
             * The App ID from the Facebook App.
             */
            'app_id' => env('FACEBOOK_APP_ID'),

            /*
             * The App Secret from the Facebook App.
             */
            'app_secret' => env('FACEBOOK_APP_SECRET'),

            /*
             * The Page ID to retrieve the leads from.
             */
            'page_id' => env('FACEBOOK_PAGE_ID'),
        ],
    ],
];
