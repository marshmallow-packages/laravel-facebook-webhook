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
            'app_id' => env('FACEBOOK_CLIENT_ID'),

            /*
             * The App Secret from the Facebook App.
             */
            'app_secret' => env('FACEBOOK_CLIENT_SECRET'),

            /*
             * The Page ID to retrieve the leads from.
             */
            'page_id' => env('FACEBOOK_PAGE_ID'),
        ],
    ],
];
