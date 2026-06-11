![alt text](https://marshmallow.dev/cdn/media/logo-red-237x46.png "marshmallow.")

# Facebook Lead Webhook for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/marshmallow/laravel-facebook-webhook.svg?style=flat-square)](https://packagist.org/packages/marshmallow/laravel-facebook-webhook)
[![Total Downloads](https://img.shields.io/packagist/dt/marshmallow/laravel-facebook-webhook.svg?style=flat-square)](https://packagist.org/packages/marshmallow/laravel-facebook-webhook)

A package to retrieve Facebook Leads with webhooks & the Graph API in Laravel.

It builds on [spatie/laravel-webhook-client](https://github.com/spatie/laravel-webhook-client) to receive and process the incoming Facebook webhook, and on [Laravel Socialite](https://socialiteproviders.com/Facebook/) for the initial Facebook authentication. On an incoming webhook, the package retrieves the lead data through the Facebook Graph API and dispatches an event on completion.

## Installation

Install the package via Composer:

```bash
composer require marshmallow/laravel-facebook-webhook
```

This package uses [spatie/laravel-webhook-client](https://github.com/spatie/laravel-webhook-client) & [Laravel Socialite](https://socialiteproviders.com/Facebook/). Please read the instructions from both packages!

Publish the migration from [spatie/laravel-webhook-client](https://github.com/spatie/laravel-webhook-client) to create the table that holds the webhook calls:

```bash
php artisan vendor:publish --provider="Spatie\WebhookClient\WebhookClientServiceProvider" --tag="migrations"
```

Make sure to add the correct config for the Spatie webhook package:

```php
'name' => 'facebook-lead',
'signing_secret' => env('FACEBOOK_CLIENT_SECRET'),
'signature_header_name' => 'X-Hub-Signature',
'signature_validator' => \Marshmallow\LaravelFacebookWebhook\SignatureValidator\FacebookSignatureValidator::class,
'process_webhook_job' => \Marshmallow\LaravelFacebookWebhook\Jobs\ProcessFacebookLeadWebhookJob::class,
```

Please see the [Socialite Base Installation Guide](https://socialiteproviders.com/usage/), then follow the provider-specific instructions below.

### Add configuration to `config/services.php`

```php
'facebook' => [
    'client_id' => env('FACEBOOK_CLIENT_ID'),
    'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
    'redirect' => env('FACEBOOK_REDIRECT_URI'),
],
```

### Publish the package config and migration

Publish the config file:

```bash
php artisan vendor:publish --tag="laravel-facebook-webhook-config"
```

Publish and run the migration:

```bash
php artisan vendor:publish --tag="laravel-facebook-webhook-migrations"
php artisan migrate
```

You may also publish both at once via the service provider:

```bash
php artisan vendor:publish --provider="Marshmallow\LaravelFacebookWebhook\LaravelFacebookWebhookServiceProvider"
```

### Environment variables

Make sure the following `.env` variables are set up:

```dotenv
FACEBOOK_CALLBACK_ROUTE= # Defaults to 'webhook-client-facebook-lead'
FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=
FACEBOOK_PAGE_ID=
FACEBOOK_REDIRECT_URI= # e.g. '/auth/facebook'
```

## Configuration

The published config file lives at `config/facebook-webhook.php`. It supports multiple webhook endpoints through the `configs` array; each entry accepts the following keys:

| Key | Default | Description |
| --- | --- | --- |
| `name` | `facebook-lead` | Name of the webhook receiving endpoint. Use `default` if you only have one. |
| `process_facebook_webhook_job` | `''` | Class that processes the Facebook lead data. Should extend `\Marshmallow\LaravelFacebookWebhook\Jobs\ProcessFacebookLeadJob`. |
| `callback_route` | `env('FACEBOOK_CALLBACK_ROUTE', 'webhook-client-facebook-lead')` | The callback route name for Facebook Leads. |
| `graph_api_version` | `v14.0` | The Graph API version used for Facebook requests. |
| `app_id` | `env('FACEBOOK_CLIENT_ID')` | The App ID from the Facebook app. |
| `app_secret` | `env('FACEBOOK_CLIENT_SECRET')` | The App Secret from the Facebook app. |
| `page_id` | `env('FACEBOOK_PAGE_ID')` | The Page ID to retrieve the leads from. |

## Setup

Create a Facebook app using the [Facebook registration instructions](https://developers.facebook.com/docs/development/register). Make sure your app has the following permissions (a Page or User access token requested by a person who can advertise on the ad account and on the Page):

-   The `ads_management` permission
-   The `leads_retrieval` permission
-   The `pages_show_list` permission
-   The `pages_read_engagement` permission
-   The `pages_manage_ads` permission

After setting up the migrations and the `.env`, run:

```bash
php artisan marshmallow:setup-facebook
```

This command guides you through authenticating with Facebook, installing the app, and installing the webhook.

## Usage

Specify which job should process the lead data via the `process_facebook_webhook_job` key in the `config/facebook-webhook.php` config file. Your job must extend `Marshmallow\LaravelFacebookWebhook\Jobs\ProcessFacebookLeadJob`, which exposes the retrieved lead data through the `$webhookData` property (a `WebhookLeadResponse` instance).

For example, create a job:

```php
use Marshmallow\LaravelFacebookWebhook\Jobs\ProcessFacebookLeadJob as MarshmallowWebhookJob;

class ProcessFacebookDataJob extends MarshmallowWebhookJob
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ray($this->webhookData);
    }
}
```

And register it in the config:

```php
'process_facebook_webhook_job' => \App\Jobs\ProcessFacebookDataJob::class,
```

## Testing

```bash
composer test
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

The MIT License (MIT). Please see the [License File](LICENSE.md) for more information.
