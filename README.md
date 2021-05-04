# Facebook Lead Webhook for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/marshmallow/laravel-facebook-webhook.svg?style=flat-square)](https://packagist.org/packages/marshmallow/laravel-facebook-webhook)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/marshmallow-packages/laravel-facebook-webhook/run-tests?label=tests)](https://github.com/marshmallow-packages/laravel-facebook-webhook/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/marshmallow-packages/laravel-facebook-webhook/Check%20&%20fix%20styling?label=code%20style)](https://github.com/marshmallow-packages/laravel-facebook-webhook/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/marshmallow/laravel-facebook-webhook.svg?style=flat-square)](https://packagist.org/packages/marshmallow/laravel-facebook-webhook)

## Installation

You can install the package via composer:

```bash
composer require marshmallow/laravel-facebook-webhook
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Marshmallow\LaravelFacebookWebhook\LaravelFacebookWebhookServiceProvider" --tag="laravel-facebook-webhook-migrations"
php artisan migrate
```

This package uses [spatie/laravel-webhook-client](https://github.com/spatie/laravel-webhook-client) & [Laravel Socialite](https://socialiteproviders.com/Facebook/)
Please read the instructions from both packages!

Publish the migrations from [spatie/laravel-webhook-client](https://github.com/spatie/laravel-webhook-client)
To create the table that holds the webhook calls, you must publish the migration with:

```bash
php artisan vendor:publish --provider="Spatie\WebhookClient\WebhookClientServiceProvider" --tag="migrations"
```

Make sure to add the correct config for the Spatie Webhook Package:

```php
'name' => 'facebook-lead',
'signing_secret' => env('FACEBOOK_CLIENT_SECRET'),
'signature_header_name' => 'X-Hub-Signature',
'signature_validator' => \Marshmallow\LaravelFacebookWebhook\SignatureValidator\FacebookSignatureValidator::class,
'process_webhook_job' => \Marshmallow\LaravelFacebookWebhook\Jobs\ProcessFacebookLeadWebhookJob::class,
```

Please see the [Base Installation Guide](https://socialiteproviders.com/usage/), then follow the provider specific instructions below.

## Add configuration to `config/services.php`

```php
'facebook' => [
  'client_id' => env('FACEBOOK_CLIENT_ID'),
  'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
  'redirect' => env('FACEBOOK_REDIRECT_URI')
],
```

You can publish the config & migration files with:

```bash
php artisan vendor:publish --provider="Marshmallow\LaravelFacebookWebhook\LaravelFacebookWebhookServiceProvider"
```

After the migration has been published, you can create the `webhook_calls` table by running the migrations:

```bash
php artisan migrate
```

Make sure to have the following .env variables setup:

```php
FACEBOOK_CALLBACK_ROUTE= #Default set 'webhook-client-facebook-lead'
FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=
FACEBOOK_PAGE_ID=
FACEBOOK_REDIRECT_URI= #'/auth/facebook'
```

## Config

This is the contents of the file that will be published at `config/facebook-webhook.php`:

```php
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
```

## Setup

Create an Facebook app using the following instructions from [Facebook](https://developers.facebook.com/docs/development/register), make sure your app has the following permissions:
A Page or User access token requested by a person who can advertise on the ad account and on the Page

-   The ads_management permission
-   The leads_retrieval permission
-   The pages_show_list permission
-   The pages_read_engagement permission
-   The pages_manage_ads permission

After setting up the migrations and the .env, run:

```bash
php artisan marshmallow:setup-facebook
```

## Usage

This package uses [spatie/laravel-webhook-client](https://github.com/spatie/laravel-webhook-client) to retrieve and process the incoming Facebook Webhook. For initial Facebook authentication it uses [Laravel Socialite](https://socialiteproviders.com/Facebook/).
On an incoming webhook, it retrieves the Lead data through the Facebook Graph API and sends an event on completion.
You can specify which job should process the Lead data in the `process_facebook_webhook_job` in the `facebook-webhook` config file.

For example make a Job:

```php
    'process_facebook_webhook_job' => \App\Jobs\ProcessFacebookDataJob::class,
```

And add it to the config:

```php
    'process_facebook_webhook_job' => \App\Jobs\ProcessFacebookDataJob::class,
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [Marshmallow](https://github.com/marshmallow-packages)
-   [Spatie](https://github.com/spatie)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

```

```
