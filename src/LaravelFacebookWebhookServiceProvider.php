<?php

namespace Marshmallow\LaravelFacebookWebhook;

use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Marshmallow\LaravelFacebookWebhook\Commands\LaravelFacebookWebhookCommand;

class LaravelFacebookWebhookServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        Route::macro('webhooks', fn (string $url, string $name = 'default') => Route::post($url, '\Spatie\WebhookClient\WebhookController')->name("webhook-client-{$name}"));

        $package
            ->name('laravel-facebook-webhook')
            ->hasConfigFile()
            ->hasRoute('web')
            ->hasMigration('create_laravel_facebook_webhook_tables')
            ->hasCommand(LaravelFacebookWebhookCommand::class);
    }
}
